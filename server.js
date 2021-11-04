const { createServer } = require('http');
const { Server } = require('socket.io');
const { Message } = require('./message');
const { DataManager } = require('./DataManager')
const path = require('path');
const url = require('url');
const mime = require('mime');
const fs = require('fs');
const process = require('process');

const msgList = {
	'1':"Success",
	'0':"Login required",
	'-1': "Input Error",
	'-2':"already exist",
	'-3':"permission denied"
};

function toJson(code, data={}, msg=null) {
	if(msgList[code] !== undefined && msg===null){
		msg = msgList[code];
	}
	return {code:code, data:data, msg:msg};
}

// host http server
const httpServer = createServer((req, res)=>{
    // default entry
    let pathname = '/index.html'
    // else
    if(url.parse(req.url).pathname != '/'){
        pathname = url.parse(req.url).pathname;
    }
    
    var filename = path.join(__dirname, "static", pathname);
	(fs.exists || path.exists)(filename, function(exists){
		if (exists) {
			fs.readFile(filename, function(err, data){
				if (err) {
					// File exists but is not readable (permissions issue?)
					res.writeHead(500, {
						"Content-Type": "text/plain"
					});
					res.write("Internal server error: could not read file");
					res.end();
					return;
				}

				// File exists and is readable
				var mimetype = mime.getType(filename);
				res.writeHead(200, {
					"Content-Type": mimetype
				});
				res.write(data);
				res.end();
				return;
			});
		}else{
			// File does not exist
			res.writeHead(404, {
				"Content-Type": "text/plain"
			});
			res.write("Requested file not found: "+filename);
			res.end();
			return;
		}
	});
});

// attach socket.io on http server
const io = new Server(httpServer);
const DM = DataManager.getInstance();

io.on('connection', (socket) => {
	console.log(socket.id + " connected to /");

	socket.on('disconnect', () => {
        console.log('one disconnected');
    });

	socket.on("tryLogin", (data) => {
		let ret = DM.loginUser(data["username"], data["password"], socket);
		if(ret){
			console.log(socket.data.username + " login in w/ id: " +socket.id);
			socket.emit("loginResp", toJson(1, DM.retriveStatus(socket)));
		} else {
			socket.emit("loginResp", toJson(-1, {}, "wrong username or password"));
		}
	})

	socket.on("tryReg", (data)=>{
		let ret = DM.newUser(data["username"], data["password"], socket);
		if(ret){
			console.log(socket.data.username + " registered & login w/ id: " +socket.id);
			socket.emit("loginResp", toJson(1, DM.retriveStatus(socket)));
		} else {
			socket.emit("loginResp", toJson(-1, {}, "invalid name, choose another one!"));
		}
	})

	socket.on("tryRetriveStatus", (data)=>{
		if(DM.loginUserByToken(data["token"], socket)){
			socket.emit("loginResp", toJson(1, DM.retriveStatus(socket)));
		} else {
			socket.emit("loginResp", toJson(0));
		}
	})

	socket.on("logout", ()=>{
		console.log(socket.data.username + " logout in w/ id: " +socket.id);
		DM.logoutUser(socket);
		socket.emit("loginResp", toJson(0));
	})

	socket.on("disconnect", (res)=>{
		DM.disconnUser(socket, false);
	})

	socket.on("tryJoinServer", (data)=>{
		let name = data["name"];
		let pw = data["password"]
		if(pw == ""){
			pw = null;
		}
		let ret = DM.joinNamespace(socket, name, pw);
		if(ret){
			socket.emit("joinServerResp", toJson(1, ret))
		} else {
			socket.emit("joinServerResp", toJson(-1, {}, "unable to join"))
		}
	})

	socket.on("tryCreateServer", (data)=>{
		let name = data["name"];
		let pw = data["password"]
		if(pw == ""){
			pw = null;
		}
		let ret = DM.createServer(socket, name, pw);
		if(ret){
			socket.emit("joinServerResp", toJson(1, ret))
		} else {
			socket.emit("joinServerResp", toJson(-1, {}, "server already exists"))
		}
	})
});

io.of(/^\/[\w_\.\-]+$/).on("connection", (socket)=>{
	console.log("id "+socket.id + " attempt to connect to "+socket.nsp.name);
	socket.emit("authUser");

	socket.on("tryAuth", data=>{
		if(DM.authServerUser(socket.nsp.name, data.username, data.token)){
			let ret = DM.fetchServer(socket.nsp.name);
			if(ret){
				socket.data.username = data.username;
				socket.data.token = data.token;
				socket.emit("syncServer", toJson(1, ret));
				console.log(socket.data.username+" w/ id "+socket.id + " joined "+socket.nsp.name);
				
				// add online socket to server list?
				// no just set status is ok...
				DM.setStatus(socket.nsp.name, socket.data.username, 1);
				// announce online status
				io.of(socket.nsp.name).emit('announceStatus', toJson(1, {
						username: socket.data.username, 
						status: 1,
						isOwner: DM.isOwner(socket.nsp.name, socket.data.username)
					})
				);
			}
		} else {
			socket.disconnect();
		}
	})

	socket.on("tryCreateRoom", data=>{
		let name = data["name"];
		if(DM.isServerOwner(socket)){
			let ret = DM.createChannel(name, socket)
			if(ret){
				io.of(socket.nsp.name).emit("createChannelResp", toJson(1, ret))
			} else {
				socket.emit("createChannelResp", toJson(-2))
			}
		} else {
			socket.emit("createChannelResp", toJson(-3))
		}
	})

	socket.on("chat", data=>{
		if(DM.authServerUser(socket.nsp.name, data.username, data.token)){
			let ret = DM.chat(socket, data);
			if(ret){
				io.of(socket.nsp.name).emit("chatResp", toJson(1, {channel:data.channel, chat:ret}));
			} else {
				socket.emit("chatResp", toJson(-1, {}, "something wrong please refresh webpage"));
			}
		} else {
			socket.disconnect();
		}
	})

	socket.on("disconnect", ()=>{
		if(socket.data.username !== undefined){
			// set offline status
			DM.setStatus(socket.nsp.name, socket.data.username, 0);
			// announce offline status
			io.of(socket.nsp.name).emit('announceStatus', toJson(1, {
					username: socket.data.username, 
					status: 0,
					isOwner: DM.isOwner(socket.nsp.name, socket.data.username)
				})
			);
		} else {
			console.log(socket.id + " disconnected from "+socket.nsp.name);
		}
		
	})
})

httpServer.listen(3456, () => {
    console.log('Server running on http://0.0.0.0:3456/');
});