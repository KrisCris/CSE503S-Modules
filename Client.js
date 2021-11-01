class Client{
    username;
    socket;
    joinedServers;
    ownedServers;
    online;

    constructor(username, socket, joinedServers=new Array(), ownedServers=new Array(), online=true){
        this.username = username;
        this.socket = socket;
        this.joinedServers = joinedServers;
        this.ownedServers = ownedServers;
        this.online = online;
    }
    
}

module.exports = { Client }