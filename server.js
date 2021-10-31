const { createServer } = require('http');
const { Server } = require('socket.io');
const { Message } = require('./message');
const path = require('path');
const url = require('url');
const mime = require('mime');
const fs = require('fs');


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

const count = io.engine.clientsCount;


io.on('connection', (socket) => {
    // to everyone except this socket
    socket.broadcast.emit('chat message', new Message(1, "New Member: " + socket.id));
    // to this socket
    socket.emit('chat message', new Message(1, 'Welcome to the server!'));

    console.log('new connection from '+socket);

    socket.on('disconnect', () => {
        console.log('disconnected');
    });



    socket.on('chat message', (msg) => {
        msg = Message.build(msg);
        console.log('message: ' + msg.getMsg());
        // to everyone
        io.emit('chat message', msg);
    });
});

httpServer.listen(3456, () => {
    console.log('Server running on http://0.0.0.0:3456/');
});
