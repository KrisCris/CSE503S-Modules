const fs = require('fs');

class DataManager {
    static instance;
    static path = "./data/"

    servers;
    users;
    tokens;

    constructor() {
        this.load();
    }

    static getInstance() {
        if (DataManager.instance === undefined) {
            DataManager.instance = new DataManager();
            return DataManager.instance;
        } else {
            return DataManager.instance;
        }
    }

    load() {
        // TODO: Read or Create from local file
        this.servers = {};
        this.users = {};
        this.tokens = {};
    }

    hasUser(username) {
        if (this.users[username] === undefined) {
            return false;
        } else {
            return true;
        }
    }

    newUser(username, password, socket) {
        if (!this.hasUser(username)) {
            let user = {
                username: username,
                password: password,
                sockets: [socket],
                online: true,
                status: 1,
                ownedServers: [],
                joinedServers: [],
                PM: {}
            };
            this.users[username] = user;
            socket.data.username = username;

            // gen token to maintain status
            let randToken = require('crypto').randomBytes(64).toString('hex');
            this.tokens[randToken] = { username: username, time: Date.now() };
            socket.data.token = randToken;
            return randToken;
        } else {
            return false;
        }
    }

    loginUser(username, password, socket) {
        if (this.hasUser(username)) {
            socket.data.username = username;
            if (this.users[username].password == password) {
                // gen token
                let randToken = require('crypto').randomBytes(64).toString('hex');
                this.tokens[randToken] = { username: username, time: Date.now() };
                socket.data.token = randToken;
                // normally this won't happen 
                if (this.users[username].sockets.includes(socket)) {
                    return randToken;
                }

                // multi device
                this.users[username].sockets.push(socket);
                return randToken;
            }
        } else {
            return false;
        }
    }

    loginUserByToken(token, socket) {
        if (this.tokens[token] !== undefined) {
            let time = this.tokens[token]["time"];
            let username = this.tokens[token]["username"];
            // remove if expired
            if ((Date.now() - time) / 1000 / 3600 / 24 > 60) {
                delete this.tokens[token];
                return false;
            } else {
                this.tokens[token]["time"] = Date.now();
                socket.data.token = token;
                socket.data.username = username;

                // normally this won't happen 
                if (this.users[username].sockets.includes(socket)) {
                    return true;
                }

                // multi device
                this.users[username].sockets.push(socket);
                return true;
            }
        }
    }

    logoutUser(socket) {
        if (socket.data.token && this.tokens[socket.data.token]) {
            delete this.tokens[socket.data.token];
            socket.data.username = undefined;
        }
        this.disconnUser(socket);
    }

    disconnUser(socket) {
        if (socket.data.username === undefined) {
            return;
        } else {
            // remove disconnected sockets
            let idx = this.users[socket.data.username].sockets.indexOf(socket);
            if (idx !== -1) {
                this.users[socket.data.username].sockets.splice(idx, 1);
            }
        }
    }

    createServer(socket, name, password = null) {
        // check availability
        if (!this.hasServer(name)) {
            // add this server under owner's name
            let username = socket.data.username;
            if (this.hasUser(username)) {
                this.users[username].ownedServers.push(name)
                // add server specs to server list
                let defChannName = name + "::default";
                let server = {
                    name: name,
                    password: password,
                    owner: {},
                    channels: {},
                    members: {},
                    banned: []
                };
                server.channels[defChannName] = {
                    chats: {
                        0: {
                            id: 0,
                            username: 'SERVER',
                            type: 0,
                            msg: ['Welcome, You can start chat!'],
                            attachment: null,
                            time: Date.now()
                        }
                    }
                };
                server.owner[username] = {
                    username: username,
                    status: 1
                }
                this.servers[name] = server
                return server;
            } else {
                return false
            }
        } else {
            return false;
        }
    }

    joinNamespace(socket, name, password = null) {
        if (this.hasServer(name)) {
            let username = socket.data.username;
            if (this.servers[name].password != password) {
                return false;
            }
            if (this.hasUser(username)) {
                if (this.users[username].joinedServers.includes(name) || this.users[username].ownedServers.includes(name)) {
                    return false;
                }
                this.users[username].joinedServers.push(name)
                // add user to the server's member list
                this.servers[name].members[username] = {
                    username: username,
                    status: 1
                }
                return this.servers[name];
            }
        }
        return false;
    }

    hasServer(name) {
        if (this.servers[name] === undefined) {
            return false;
        } else {
            return true;
        }
    }

    retriveStatus(socket) {
        let username = socket.data.username;

        let data = {
            username: socket.data.username,
            token: socket.data.token,
            ownedServers: this.users[username].ownedServers,
            joinedServers: this.users[username].joinedServers,
            PM: this.users[username].PM
        }

        return data
    }

    fetchServer(name) {
        if (this.hasServer(name)) {
            return this.servers[name];
        } else {
            return false
        }
    }

    authServerUser(serverName, username, token) {
        if (this.hasServer(serverName) && this.hasUser(username)) {
            if (this.tokens[token] !== undefined && this.tokens[token]["username"] == username) {
                if (this.users[username]['ownedServers'].includes(serverName) || this.users[username]['joinedServers'].includes(serverName)) {
                    let banned = this.servers[serverName]["banned"];
                    let members = Object.keys(this.servers[serverName]['members']);
                    let owners = Object.keys(this.servers[serverName]['owner']);
                    if (!banned.includes(username) && (members.includes(username) || owners.includes(username))) {
                        return true;
                    }
                }
            }
        }
        return false
    }

    isServerOwner(socket) {
        if (this.authServerUser(socket.nsp.name, socket.data.username, socket.data.token)) {
            if (this.servers[socket.nsp.name].owner[socket.data.username]) {
                return true;
            }
        }
        return false;
    }

    createChannel(channelName, socket) {
        channelName = socket.nsp.name + "::" + channelName;
        if (this.servers[socket.nsp.name].channels[channelName] === undefined) {
            this.servers[socket.nsp.name].channels[channelName] = {
                chats: {
                    0: {
                        id: 0,
                        username: 'SERVER',
                        type: 0,
                        msg: ['Welcome, You can start chat!'],
                        attachment: null,
                        time: Date.now()
                    }
                }
            }
            return this.servers[socket.nsp.name];
        } else {
            return false;
        }
    }

    chat(socket, data) {
        // {
        // 	channel: selectedChannel[0],
        // 	msg: strArr,
        // 	attachment: null,
        // 	token: localStorage.token,
        // 	username: localStorage.username
        // }
        if (this.servers[socket.nsp.name].channels[data.channel]) {
            let chats = this.servers[socket.nsp.name].channels[data.channel].chats;
            let keys = Object.keys(chats);
            let newId = Number(keys[keys.length - 1]) + 1;
            let c = {
                id: newId,
                username: data.username,
                type: 1,
                msg: data.msg,
                attachment: data.attachment,
                time: Date.now()
            };
            chats[newId] = c;
            return c;
        }
        return false;
    }

    globalNoti(serverName, msg) {
        for (let key of Object.keys(this.servers[serverName].channels)) {
            let chats = this.servers[serverName].channels[key].chats;
            let keys = Object.keys(chats);
            let newId = Number(keys[keys.length - 1]) + 1;
            let c = {
                id: newId,
                username: 'SERVER',
                type: 0,
                msg: [msg],
                attachment: null,
                time: Date.now()
            };
            chats[newId] = c;
            return c;
        }
    }

    isOwner(serverName, username) {
        if (this.servers[serverName].owner[username]) {
            return true;
        } else {
            return false;
        }
    }

    setStatus(serverName, username, status) {
        if (this.isOwner(serverName, username)) {
            this.servers[serverName].owner[username].status = status;
        } else {
            this.servers[serverName].members[username].status = status;
        }
    }

    kick(username, serverName) {
        delete this.servers[serverName].members[username];
        let idx = this.users[username].joinedServers.indexOf(serverName);
        if (idx != -1) {
            this.users[username].joinedServers.pop(idx);
        }
        return true;
    }

    ban(username, serverName) {
        delete this.servers[serverName].members[username];
        let idx = this.users[username].joinedServers.indexOf(serverName);
        if (idx != -1) {
            this.users[username].joinedServers.pop(idx);
        }
        this.servers[serverName].banned.push(username);
        return true;
    }

    initPM(from, to) {
        if (this.hasUser(from) && this.hasServer(to)) {
            if (!this.users[from]['PM'][to]) {
                this.users[from]['PM'][to] = {
                    chats: {
                        0: {
                            id: 0,
                            username: 'SERVER',
                            type: 0,
                            msg: ['Welcome, You can start chat!'],
                            attachment: null,
                            time: Date.now()
                        }
                    }
                };
            }
            if (!this.users[to]['PM'][from]) {
                this.users[to]['PM'][from] = {
                    chats: {
                        0: {
                            id: 0,
                            username: 'SERVER',
                            type: 0,
                            msg: ['Welcome, You can start chat!'],
                            attachment: null,
                            time: Date.now()
                        }
                    }
                }
            }
        }
        return true;
    }

}

module.exports = { DataManager }