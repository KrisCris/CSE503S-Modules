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
                ownedServers:{ },
                joinedServers:{ }
            };
            this.users[username] = user;
            socket.data.username = username;

            // gen token to maintain status
            let randToken = require('crypto').randomBytes(64).toString('hex');
            this.tokens[randToken] = {username:username, time:Date.now()};
            socket.token = randToken;
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

                // normally this won't happen 
                if(this.users[username].sockets.includes(socket)){
                    return randToken;
                }

                // multi device
                this.users[username].sockets.push(socket);
                return token;
            }
        }
    }

    logoutUser(socket){
        if(socket.data.token && this.tokens[socket.data.token]){
            delete this.tokens[socket.data.token]
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
}

module.exports = { DataManager }