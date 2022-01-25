const { createServer } = require('http');
const { Server } = require('socket.io');
const { DataManager } = require('./DataManager')
const path = require('path');
const url = require('url');
const mime = require('mime');
const fs = require('fs');
const process = require('process');

const msgList = {
	'1': "Success",
	'0': "Login required",
	'-1': "Input Error",
	'-2': "already exist",
	'-3': "permission denied"
};

function toJson(code, data = {}, msg = null) {
	if (msgList[code] !== undefined && msg === null) {
		msg = msgList[code];
	}
	return { code: code, data: data, msg: msg };
}

// host http server
const httpServer = createServer((req, res) => {

	let filename = "";
	if (url.parse(req.url).pathname == '/') {
		filename = path.join(__dirname, "../static", '/index.html');
	} else if(url.parse(req.url).pathname.startsWith('/import')){
		filename = path.join(__dirname, "../node_modules", url.parse(req.url).pathname);
	} else {
		filename = path.join(__dirname, "../static", url.parse(req.url).pathname);
	}

	
	(fs.exists || path.exists)(filename, function (exists) {
		if (exists) {
			fs.readFile(filename, function (err, data) {
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
		} else {
			// File does not exist
			res.writeHead(404, {
				"Content-Type": "text/plain"
			});
			res.write("Requested file not found: " + filename);
			res.end();
			return;
		}
	});
});

// attach socket.io on http server
const io = new Server(httpServer,{
	maxHttpBufferSize: 1e8
});
const DM = DataManager.getInstance();

// connection to lobby / PM
io.on('connection', (socket) => {
	console.log(socket.id + " connected to /");

	socket.on("disconnect", (res) => {
		console.log(socket.data.username+' disconnected');
		DM.disconnUser(socket, false);
	})

	socket.on("tryLogin", (data) => {
		let ret = DM.loginUser(data["username"], data["password"], socket);
		if (ret) {
			console.log(socket.data.username + " login in w/ id: " + socket.id);
			socket.emit("loginResp", toJson(1, DM.retriveStatus(socket)));
		} else {
			socket.emit("loginResp", toJson(-1, {}, "wrong username or password"));
		}
	})

	socket.on("tryReg", (data) => {
		let ret = DM.newUser(data["username"], data["password"], socket);
		if (ret) {
			console.log(socket.data.username + " registered & login w/ id: " + socket.id);
			socket.emit("loginResp", toJson(1, DM.retriveStatus(socket)));
		} else {
			socket.emit("loginResp", toJson(-1, {}, "invalid name, choose another one!"));
		}
	})

	socket.on("tryRetriveStatus", (data) => {
		if (DM.loginUserByToken(data["token"], socket)) {
			console.log(socket.data.username + " logout in w/ id: " + socket.id);
			socket.emit("loginResp", toJson(1, DM.retriveStatus(socket)));
		} else {
			socket.emit("loginResp", toJson(0));
		}
	})

	socket.on("logout", () => {
		console.log(socket.data.username + " logout in w/ id: " + socket.id);
		DM.logoutUser(socket);
		socket.emit("loginResp", toJson(0));
	})

	socket.on('typing', data => {
		if (socket.data.username && DM.users[data.target]) {
			for (let s of DM.users[data.target].sockets) {
				s.emit('typing', toJson(1, { target: socket.data.username }));
			}
		}
	})

	socket.on("tryJoinServer", (data) => {
		let name = data["name"];
		let pw = data["password"]
		if (pw == "") {
			pw = null;
		}
		let ret = DM.joinNamespace(socket, name, pw);
		if (ret) {
			socket.emit("joinServerResp", toJson(1, ret))
		} else {
			socket.emit("joinServerResp", toJson(-1, {}, "unable to join"))
		}
	})

	socket.on("tryCreateServer", (data) => {
		let name = data["name"];
		let pw = data["password"]
		if (pw == "") {
			pw = null;
		}
		let ret = DM.createServer(socket, name, pw);
		if (ret) {
			socket.emit("joinServerResp", toJson(1, ret))
		} else {
			socket.emit("joinServerResp", toJson(-1, {}, "server already exists"))
		}
	})

	socket.on("PM", (data) => {
		let ret = DM.PM(socket, data);
		if (ret) {
			for (let s of DM.users[socket.data.username].sockets) {
				s.emit('incomePM', toJson(1, { target: data.target, chat: ret }));
			}
			for (let s of DM.users[data.target].sockets) {
				s.emit('incomePM', toJson(1, { target: socket.data.username, chat: ret }));
			}
		}
	})
});

// server connection
io.of(/^\/[\w_\.\-]+$/).on("connection", (socket) => {
	console.log("id " + socket.id + " attempt to connect to " + socket.nsp.name);
	socket.emit("authUser");

	socket.on("tryAuth", data => {
		if (DM.authServerUser(socket.nsp.name, data.username, data.token)) {
			let ret = DM.fetchServer(socket.nsp.name);
			if (ret) {
				socket.data.username = data.username;
				socket.data.token = data.token;
				socket.emit("syncServer", toJson(1, ret));
				console.log(socket.data.username + " w/ id " + socket.id + " joined " + socket.nsp.name);

				// add online socket to server list?
				// no, just set status is ok...
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
			socket.data.username = undefined;
			socket.data.token = undefined;
			socket.disconnect();
		}
	})

	socket.on("tryCreateRoom", data => {
		let name = data["name"];
		if (DM.isServerOwner(socket)) {
			let ret = DM.createChannel(name, socket)
			if (ret) {
				io.of(socket.nsp.name).emit("createChannelResp", toJson(1, ret))
			} else {
				socket.emit("createChannelResp", toJson(-2))
			}
		} else {
			socket.emit("createChannelResp", toJson(-3))
		}
	})

	socket.on("chat", data => {
		if (DM.authServerUser(socket.nsp.name, data.username, data.token)) {
			let ret = DM.chat(socket, data);
			if (ret) {
				io.of(socket.nsp.name).emit("chatResp", toJson(1, { channel: data.channel, chat: ret }));
			} else {
				socket.emit("chatResp", toJson(-1, {}, "something wrong please refresh webpage"));
			}
		} else {
			socket.disconnect();
		}
	})

	socket.on('tryKick', data => {
		if (DM.authServerUser(socket.nsp.name, socket.data.username, socket.data.token)) {
			if (DM.isServerOwner(socket)) {
				DM.kick(data.username, socket.nsp.name);
				io.of(socket.nsp.name).emit("authUser");
				let ret = DM.globalNoti(socket.nsp.name, data.username + " is kicked from this server!")
				for (let chann of Object.keys(DM.servers[socket.nsp.name].channels)) {
					io.of(socket.nsp.name).emit(
						"chatResp",
						toJson(1, {
							channel: chann,
							chat: ret
						}));
				}
			}
		}
	})

	socket.on('tryPM', data => {
		let ret = DM.initPM(socket.data.username, data.target, socket);
		if (ret) {
			for (let s of DM.users[socket.data.username].sockets) {
				s.emit('initPMResp', toJson(1, { target: data.target, chat: ret }));
			}
			for (let s of DM.users[data.target].sockets) {
				s.emit('incomePM', toJson(1, { target: socket.data.username, chat: ret }));
			}
		} else {
			socket.emit('initPMResp', toJson(-3, {}, "invalid request!"));
		}
	})

	socket.on('tryBan', data => {
		if (DM.authServerUser(socket.nsp.name, socket.data.username, socket.data.token)) {
			if (DM.isServerOwner(socket)) {
				DM.ban(data.username, socket.nsp.name);
				io.of(socket.nsp.name).emit("authUser");
				let ret = DM.globalNoti(socket.nsp.name, data.username + " is banned from this server!")
				for (let chann of Object.keys(DM.servers[socket.nsp.name].channels)) {
					io.of(socket.nsp.name).emit(
						"chatResp",
						toJson(1, {
							channel: chann,
							chat: ret
						}));
				}
			}
		}
	})

	socket.on('typing', data => {
		socket.broadcast.emit('typing', toJson(1, { username: socket.data.username, channel: data.channel }));
	})

	socket.on("disconnect", () => {
		if (socket.data.username !== undefined) {
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
			console.log(socket.id + " disconnected from " + socket.nsp.name);
		}
	})
})

httpServer.listen(2333, () => {
	console.log('Server running on http://127.0.0.1:2333/');
});