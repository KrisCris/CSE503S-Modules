const fs = require('fs');

class DataManager{
    static instance;
    static path = "./data/"

    servers;
    users;
    tokens;

    constructor(){
        this.load();
    }

    static getInstance(){
        if(DataManager.instance === undefined){
            DataManager.instance = new DataManager();
            return DataManager.instance;
        } else {
            return DataManager.instance;
        }
    }

    load(){
        // TODO: Read or Create from local file
        this.servers = {};
        this.users = {};
        this.tokens = {};
    }

    hasUser(username){
        if(this.users[username] === undefined){
            return false;
        } else {
            return true;
        }
    }

    newUser(username, password, socket){
        if(!this.hasUser(username)){
            let user = {
                username: username,
                password: password,
                sockets: [socket],
                online: true,
                status: 1,
                ownedServers:[],
                joinedServers:[]
            };
            this.users[username] = user;
            socket.data.username = username;

            // gen token to maintain status
            let randToken = require('crypto').randomBytes(64).toString('hex');
            this.tokens[randToken] = {username:username, time:Date.now()};
            socket.data.token = randToken;
            return randToken;
        } else {
            return false;
        }
    }

    loginUser(username, password, socket){
        if(this.hasUser(username)){
            socket.data.username = username;
            if(this.users[username].password == password){
                // gen token
                let randToken = require('crypto').randomBytes(64).toString('hex');
                this.tokens[randToken] = {username:username, time:Date.now()};
                socket.data.token = randToken;
                // normally this won't happen 
                if(this.users[username].sockets.includes(socket)){
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

    loginUserByToken(token, socket){
        if(this.tokens[token] !== undefined){
            let time = this.tokens[token]["time"];
            let username = this.tokens[token]["username"];
            // remove if expired
            if((Date.now()-time)/1000/3600/24 > 60){
                delete this.tokens[token];
                return false;
            } else {
                this.tokens[token]["time"] = Date.now();
                socket.data.token = token;
                socket.data.username = username;

                // normally this won't happen 
                if(this.users[username].sockets.includes(socket)){
                    return true;
                }

                // multi device
                this.users[username].sockets.push(socket);
                return true;
            }
        }
    }

    logoutUser(socket){
        if(socket.data.token && this.tokens[socket.data.token]){
            delete this.tokens[socket.data.token];
            socket.data.username = undefined;
        }
        this.disconnUser(socket);
    }

    disconnUser(socket){
        if(socket.data.username === undefined){
            return;
        } else {
            // remove disconnected sockets
            let idx = this.users[socket.data.username].sockets.indexOf(socket);
            if (idx !== -1) {
                this.users[socket.data.username].sockets.splice(idx, 1);
            }
        }
    }

    createNamespace(socket, name, password = null){
        // check availability
        if(!this.hasNamespace(name)){
            // add this server under owner's name
            let username = socket.data.username;
            if(this.hasUser(username)){
                this.users[username].ownedServers.push(name)
                // add server specs to server list
                let server = {
                    name: name,
                    password: password,
                    owner:[username],
                    channels:{
                        "Default":{
                            chats:{}
                        }
                    },
                    members:[],
                    banned:[]
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

    joinNamespace(socket, name, password = null){
        if(this.hasNamespace(name)){
            let username = socket.data.username;
            if(this.servers[name].password != password){
                return false;
            }
            if(this.hasUser(username)){
                if(this.users[username].joinedServers.includes(name) || this.users[username].ownedServers.includes(name)){
                    return false;
                }
                this.users[username].joinedServers.push(name)
                // add user to the server's member list
                this.servers[name].members.push(username)
                return this.servers[name];
            }
        }
        return false;
    }

    hasNamespace(name){
        if(this.servers[name] === undefined){
            return false;
        } else {
            return true;
        }
    }

    retriveStatus(socket){
        let username = socket.data.username;

        let data = {
            username: socket.data.username,
            token:socket.data.token,
            ownedServers:this.users[username].ownedServers,
            joinedServers:this.users[username].joinedServers
        }

        return data
    }

    fetchServer(name){
        if(this.hasNamespace(name)){
            return this.servers[name];
        } else {
            return false
        }
    }

    authServerUser(serverName, username, token){
        if(this.hasNamespace(serverName) && this.hasUser(username)){
            if(this.tokens[token] !== undefined && this.tokens[token]["username"]==username){
                if(this.users[username]['ownedServers'].includes(serverName) || this.users[username]['joinedServers'].includes(serverName)){
                    if(!this.servers[serverName]["banned"].includes(username) && (this.servers[serverName]['members'].includes(username) || this.servers[serverName]['owner'].includes(username))){
                        return true;
                    }
                }
            }
        }
        return false
    }
}

module.exports = { DataManager }