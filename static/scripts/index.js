// defalut namespace
const dftSocket = io();
const servers = {};
const PM = {};

dftSocket.on("connect", () => {
    document.getElementById("connStatus").setAttribute("src", "./res/online.png");
    if (localStorage.token !== undefined) {
        dftSocket.emit("tryRetriveStatus", { token: localStorage.token });
    }
})

dftSocket.on("disconnect", () => {
    document.getElementById("connStatus").setAttribute("src", "data:image/png;base64,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")
})

// login
const username = document.getElementById("username");
const password = document.getElementById("password");
const btnLogin = document.getElementById("btnLogin");
const btnReg = document.getElementById("btnReg");
const btnLogout = document.getElementById("btnLogout");

dftSocket.on("loginResp", (res) => {
    if (res["code"] == 1) {
        localStorage.token = res['data']['token'];
        localStorage.username = res['data']['username'];
        initUserFunc(res['data'])
    } else {
        if (res["code"] == -1) {
            alert(res['msg']);
        }
        initGuestFunc();
    }
})

btnLogin.addEventListener('click', e => {
    let uname = username.value;
    let upw = password.value;
    let regex = /^[\w_\.\-]+$/;
    if (!regex.test(uname) || upw == '') {
        alert("Invalid Input");
    } else {
        dftSocket.emit("tryLogin", { username: uname, password: upw });
    }
});

btnReg.addEventListener('click', e => {
    let uname = username.value;
    let upw = password.value;
    let regex = /^[\w_\.\-]+$/;
    if (!regex.test(uname) || upw == '') {
        alert("Invalid Input");
    } else {
        dftSocket.emit("tryReg", { username: uname, password: upw });
    }
})

btnLogout.addEventListener('click', e => {
    dftSocket.emit("logout");
    for (let key in servers) {
        servers[key].socket.disconnect();
    }
    for (var server in servers) delete servers[server];
});

const loginPage = document.getElementsByClassName("login")[0];
const chatPage = document.getElementsByClassName("chatroom")[0];
const btnUser = document.getElementById("btnUser");

const typing_name = document.getElementById('typing-name');
const typing_indicator = document.getElementById('typing-indicator');

function handleServers(server_name) {
    let server = io(server_name);

    server.on("authUser", () => {
        server.emit("tryAuth", { username: localStorage.username, token: localStorage.token })
    });

    server.on("syncServer", data => {
        servers[server_name] = {
            socket: server,
            data: data['data']
        };
        updateChats();
    });

    server.on('createChannelResp', data => {
        if (data['code'] == 1) {
            servers[server_name].data = data["data"];
            updateChats();
        } else {
            alert(data['msg']);
        }
    });

    server.on('announceStatus', data => {
        data = data['data'];
        let owner = servers[server_name]['data']['owner'];
        let members = servers[server_name]['data']['members'];

        if (data.isOwner) {
            if (owner[data.username]) {
                owner[data.username]['status'] = data['status'];
            } else {
                owner[data.username] = { username: data.username, status: data.status };
            }
        } else {
            if (members[data.username]) {
                members[data.username]['status'] = data['status'];
            } else {
                members[data.username] = { username: data.username, status: data.status };
            }
        }
        // need to implement partial update;
        updateChats();
    });

    server.on('chatResp', data => {
        if (data['code'] == 1) {
            let chat = data['data']['chat'];
            let channel = data['data']['channel']
            servers[server_name]['data']['channels'][channel]['chats'][chat['id']] = chat;
            if (selectedServer[0] == server_name) {
                if (selectedChannel[0] == channel) {
                    // show msg
                    let cid = chat['id'];
                    let username = chat["username"];
                    let type = chat["type"];
                    let msgArr = chat["msg"];
                    let attachment = chat["attachment"];
                    let tsp = chat["time"];
                    let dateArr = new Date(tsp).toString().split(" ");
                    let time = " " + dateArr[0] + " " + dateArr[1] + " " + dateArr[2] + " " + dateArr[3] + " " + dateArr[4] + " :";

                    let li = document.createElement('li');
                    let p = document.createElement('p');
                    let sp1 = document.createElement("span");
                    let sp2 = document.createElement("span");
                    li.setAttribute("id", cid);
                    li.setAttribute("type", type);
                    sp1.append(username);
                    sp1.classList.add("name");
                    sp2.append(time)
                    sp2.classList.add("time");
                    p.append(sp1);
                    p.append(sp2);
                    let a = document.createElement('a');
                    a.setAttribute("href", "#");
                    for (let msg of msgArr) {
                        a.append(msg);
                        a.append(document.createElement("br"));
                    }
                    a.removeChild(a.lastChild)
                    // attachment?
                    li.append(p);
                    li.append(a);
                    chat_list.append(li);
                } else {
                    // hightlight that channel
                }
            } else {
                // hightlight that server
            }

        } else {
            alert(data['msg']);
        }
    });

    server.on('typing', data => {
        if (!typing_indicator.classList.contains('displayNone')) {
            let interval = 2000;
            removeAllChildNodes(typing_name);
            typing_name.append(data['data'].username);
            typing_indicator.classList.remove('displayNone');
            setTimeout(() => {
                typing_indicator.classList.add('displayNone');
                removeAllChildNodes(typing_name);
            }, interval);
        }
    });

    server.on("disconnect", () => {
        delete servers[server_name];
        removeAllChildNodes(server_list)
        removeAllChildNodes(room_list);
        removeAllChildNodes(member_list);
        removeAllChildNodes(chat_list);
        updateChats();
    });
}

function initUserFunc(data) {
    removeAllChildNodes(username);
    removeAllChildNodes(password);

    let ownedServers = data['ownedServers'];
    let joinedServers = data['joinedServers'];

    removeAllChildNodes(btnUser);
    btnUser.append(data['username'])

    for (let s of ownedServers) {
        handleServers(s);
    }

    for (let s of joinedServers) {
        handleServers(s);
    }

    chatPage.classList.remove("displayNone");
    loginPage.classList.add("displayNone");
}

function initGuestFunc() {
    localStorage.token = undefined;
    localStorage.username = undefined;
    // disconn all chat server
    for (let key in servers) {
        servers[key].socket.disconnect();
    }
    for (var server in servers) delete servers[server];

    selectedServer = [null, null];
    selectedChannel = [null, null];

    chatPage.classList.add("displayNone");
    loginPage.classList.remove("displayNone");
    removeAllChildNodes(inputServerName);
    removeAllChildNodes(inputServerPW);
    removeAllChildNodes(server_list)
    removeAllChildNodes(room_list);
    removeAllChildNodes(member_list);
    removeAllChildNodes(chat_list);
    removeAllChildNodes(textarea);
    textarea.setAttribute("contenteditable", false);
}


const server_list = document.getElementsByClassName("server-list")[0];
const room_list = document.getElementsByClassName("room-list")[0];
const member_list = document.getElementsByClassName("member-list")[0];
const chat_list = document.getElementsByClassName("chat-list")[0];


var selectedServer = [null, null];
var selectedChannel = [null, null];

function updateChats() {
    removeAllChildNodes(server_list);
    if (servers[selectedServer[0]] === undefined) {
        selectedServer[0] = null;
        selectedServer[1] = null;
    }
    // private message btn
    let li = document.createElement('li');
    let a = document.createElement('a');
    a.setAttribute("href", "#");
    a.append("Private Messages");
    li.append(a);
    a.addEventListener('click', (e) => {
        processServerSelection(key, li, true);
    })
    li.addEventListener('click', (e) => {
        processServerSelection(key, li, true);
    })
    li.classList.add('PM');
    server_list.append(li);



    // draw all servers joined so far
    for (let key in servers) {
        let li = document.createElement('li');
        let a = document.createElement('a');
        a.setAttribute("href", "#");
        a.append(servers[key].data.name);
        li.append(a);
        a.addEventListener('click', (e) => {
            processServerSelection(key, li);
        })
        li.addEventListener('click', (e) => {
            processServerSelection(key, li);
        })
        if (selectedServer[0] === null) {
            processServerSelection(key, li);
        } else {
            if (key == selectedServer[0]) {
                processServerSelection(key, li);
            }
        }
        server_list.append(li);

    }
}

const status_list = {
    1: 'online',
    0: 'offline'
};

const newChannDiv = document.getElementsByClassName('newChannel')[0];
const roomsDiv = document.getElementsByClassName('room-list')[0];
const chatsDiv = document.getElementsByClassName('chats')[0];
const usersDiv = document.getElementsByClassName('members')[0];

function serverStyle() {
    // show
    newChannDiv.style.height = 6.2 + 'rem';
    newChannDiv.classList.remove('displayNone');
    usersDiv.style.width = 15 + '%';
    usersDiv.classList.remove('displayNone');

    roomsDiv.style.top = 6.2 + 'rem';
    chatsDiv.style.right = 15 + '%';
}

function pmStyle() {
    // hide
    newChannDiv.style.height = 0 + 'rem';
    newChannDiv.classList.add('displayNone');
    usersDiv.style.width = 0 + 'rem';
    usersDiv.classList.add('displayNone');

    // extand
    roomsDiv.style.top = 0 + 'rem';
    chatsDiv.style.right = 0 + 'rem';
}

function processServerSelection(key, li, isPM = false) {
    if (isPM) {
        pmStyle();
    } else {
        serverStyle();
    }
    // select
    selectedServer[0] = key;
    if (selectedServer[1] != null) {
        if (selectedServer[1] != li)
            selectedServer[1].classList.remove("selected");
    }
    selectedServer[1] = li;
    li.classList.add("selected")

    // render server data
    // 1. channel list
    if (servers[key].data.channels[selectedChannel[0]]) {
        // prev selected a chann in this server, keep it
    } else {
        // select the default one
        selectedChannel = [key + '::default', null];
    }

    removeAllChildNodes(room_list);

    for (let channelKey in servers[key]["data"]["channels"]) {
        let li = document.createElement("li");
        let a = document.createElement('a');
        a.setAttribute("href", "#");
        a.append(channelKey);
        li.append(a);
        room_list.append(li);

        if (selectedChannel[0]) {
            if (selectedChannel[0] == channelKey) {
                // 2. chann select, render chats
                processChannelSelection(channelKey, li);
            }
        } else {
            processChannelSelection(channelKey, li);
        }

        a.addEventListener('click', (e) => {
            processChannelSelection(channelKey, li);
        })
        li.addEventListener('click', (e) => {
            processChannelSelection(channelKey, li);
        })
    }

    // 3. show users
    removeAllChildNodes(member_list);
    let owner = servers[key]["data"]["owner"];
    let orderedOwner = {};
    for (let username in owner) {
        if (orderedOwner[owner[username].status]) {
            orderedOwner[owner[username].status].push(owner[username]);
        } else {
            orderedOwner[owner[username].status] = [];
            orderedOwner[owner[username].status].push(owner[username]);
        }
    }
    for (let status of Object.keys(orderedOwner).reverse()) {
        for (let user of orderedOwner[status]) {
            let li = document.createElement('li')
            let a = document.createElement('a')
            a.setAttribute("href", "#");
            a.append(user.username);
            li.append(a);
            li.classList.add('owner');
            li.classList.add(status_list[status])
            li.addEventListener("contextmenu", e => {
                e.preventDefault();
                hideMenu();
                if (user.username != localStorage.username && status == 1) {
                    // you can't kick server owner
                    userMenu.setAttribute('username', user.username);
                    userMenu.setAttribute('server', selectedServer[0]);
                    document.getElementById("userMenu").style.top = e.pageY + 'px';
                    document.getElementById("userMenu").style.left = (e.pageX - 6 * parseFloat(getComputedStyle(document.documentElement).fontSize)) + 'px';
                    document.getElementById("userMenu").classList.remove("displayNone");
                }


            })

            member_list.append(li);
        }
    }

    let members = servers[key]["data"]["members"];
    let orderedUser = {};
    for (let username in members) {
        if (orderedUser[members[username].status]) {
            orderedUser[members[username].status].push(members[username]);
        } else {
            orderedUser[members[username].status] = [];
            orderedUser[members[username].status].push(members[username]);
        }
    }
    for (let status of Object.keys(orderedUser).reverse()) {
        for (let user of orderedUser[status]) {
            let li = document.createElement('li')
            let a = document.createElement('a')
            a.setAttribute("href", "#");
            a.append(user.username);
            li.append(a);
            li.classList.add('user');
            li.classList.add(status_list[status])

            // right click menu
            li.addEventListener("contextmenu", e => {
                e.preventDefault();
                hideMenu();
                if (user.username != localStorage.username && status == 1) {
                    if (owner[localStorage.username]) {
                        userMenuAdmin.setAttribute('username', user.username);
                        userMenuAdmin.setAttribute('server', selectedServer[0]);
                        userMenuAdmin.style.top = e.pageY + 'px';
                        userMenuAdmin.style.left = (e.pageX - 6 * parseFloat(getComputedStyle(document.documentElement).fontSize)) + 'px';
                        userMenuAdmin.classList.remove("displayNone");
                    } else {
                        userMenu.setAttribute('username', user.username);
                        userMenu.setAttribute('server', selectedServer[0]);
                        userMenu.style.top = e.pageY + 'px';
                        userMenu.style.left = (e.pageX - 6 * parseFloat(getComputedStyle(document.documentElement).fontSize)) + 'px';
                        userMenu.classList.remove("displayNone");
                    }
                }
            })
            member_list.append(li);
        }
    }
}

// hide right click menu
document.addEventListener('click', hideMenu);
document.addEventListener('contextmenu', e => {
    // e.preventDefault();
    hideMenu();
}, true);

const userMenuAdmin = document.getElementById("userMenuAdmin");
const userMenu = document.getElementById("userMenu");
function hideMenu() {
    userMenuAdmin.classList.add("displayNone");
    userMenu.classList.add("displayNone");
    userMenu.removeAttribute('server');
    userMenuAdmin.removeAttribute('server');
    userMenu.removeAttribute('username');
    userMenuAdmin.removeAttribute('username');
}

const pm = document.getElementById('pm');
const at = document.getElementById('at');
const kick = document.getElementById('kick');
const ban = document.getElementById('ban');
const upm = document.getElementById('upm');
const uat = document.getElementById('uat');

pm.addEventListener('click', e => {
    let u = e.target.parentNode.getAttribute('username');
    let s = e.target.parentNode.getAttribute('server');
    console.log('you pm ' + u + " from " + s);
})

at.addEventListener('click', e => {
    let u = e.target.parentNode.getAttribute('username');
    let s = e.target.parentNode.getAttribute('server');
    console.log('you at ' + u + " from " + s);
})

upm.addEventListener('click', e => {
    let u = e.target.parentNode.getAttribute('username');
    let s = e.target.parentNode.getAttribute('server');
    console.log('you pm ' + u + " from " + s);
})

uat.addEventListener('click', e => {
    let u = e.target.parentNode.getAttribute('username');
    let s = e.target.parentNode.getAttribute('server');
    console.log('you at ' + u + " from " + s);
})

kick.addEventListener('click', e => {
    let u = e.target.parentNode.getAttribute('username');
    let s = e.target.parentNode.getAttribute('server');
    servers[s].socket.emit('tryKick', { username: u });
})

ban.addEventListener('click', e => {
    let u = e.target.parentNode.getAttribute('username');
    let s = e.target.parentNode.getAttribute('server');
    servers[s].socket.emit('tryBan', { username: u });
})

function processChannelSelection(key, li) {
    // select
    selectedChannel[0] = key;
    if (selectedChannel[1] != null) {
        if (selectedChannel[1] != li)
            selectedChannel[1].classList.remove("selected");
    }
    selectedChannel[1] = li;
    li.classList.add("selected")

    // display chats
    // structure:
    // chat[id=0] = {
    //     id: 0,
    //     username: 'SERVER',
    //     type: 0,
    //     msg: ['Welcome, You can start chat!'],
    //     attachment: null,
    //     time: Date.now()
    // };
    removeAllChildNodes(chat_list);
    let chats = servers[selectedServer[0]]["data"]["channels"][key]["chats"]
    for (let cid in chats) {
        //display chats
        let username = chats[cid]["username"];
        let type = chats[cid]["type"];
        let msgArr = chats[cid]["msg"];
        let attachment = chats[cid]["attachment"];
        let tsp = chats[cid]["time"];

        let dateArr = new Date(tsp).toString().split(" ");
        let time = " " + dateArr[0] + " " + dateArr[1] + " " + dateArr[2] + " " + dateArr[3] + " " + dateArr[4] + " :";
        let li = document.createElement('li');
        let p = document.createElement('p');
        let sp1 = document.createElement("span");
        let sp2 = document.createElement("span");
        li.setAttribute("id", cid);
        li.setAttribute("type", type);
        sp1.append(username);
        sp1.classList.add("name");
        sp2.append(time)
        sp2.classList.add("time");
        p.append(sp1);
        p.append(sp2);
        let a = document.createElement('a');
        a.setAttribute("href", "#");
        for (let msg of msgArr) {
            a.append(msg);
            a.append(document.createElement("br"));
        }
        a.removeChild(a.lastChild)
        // attachment?

        li.append(p);
        li.append(a);
        chat_list.append(li);
    }
    textarea.setAttribute("contenteditable", true);
}

// add server
const inputServerName = document.getElementById("inputServerName");
const inputServerPW = document.getElementById("inputServerPW");
const btnJoinServer = document.getElementById("btnJoinServer");
const btnNewServer = document.getElementById("btnNewServer");

btnJoinServer.addEventListener('click', e => {
    let sname = inputServerName.value;
    let spw = inputServerPW.value;
    let regex = /^[\w_\.\-]+$/;
    if (!regex.test(sname)) {
        alert("No space or special characters");
    } else {
        dftSocket.emit("tryJoinServer", { name: '/' + sname, password: spw });
    }
});

btnNewServer.addEventListener('click', e => {
    let sname = inputServerName.value;
    let spw = inputServerPW.value;
    let regex = /^[\w_\.\-]+$/;
    if (!regex.test(sname)) {
        alert("No space or special characters");
    } else {
        dftSocket.emit("tryCreateServer", { name: '/' + sname, password: spw });
    }
})

dftSocket.on("joinServerResp", res => {
    if (res["code"] == 1) {
        handleServers(res['data']['name']);

    } else {
        alert(res["msg"]);
    }
})

const inputChannelName = document.getElementById("inputChannelName");
const btnCreateChannel = document.getElementById("btnCreateChannel");

btnCreateChannel.addEventListener('click', e => {
    let cname = inputChannelName.value;
    let regex = /^[\w_\.\-]+$/;
    if (!regex.test(cname)) {
        alert("No space or special characters");
    } else {
        // exists on client side
        if (selectedServer[0] && servers[selectedServer[0]]) {
            // user is owner
            if (servers[selectedServer[0]].data['owner'][localStorage.username]) {
                servers[selectedServer[0]].socket.emit('tryCreateRoom', { name: cname });
            } else {
                alert('your have no permission to do that, contact server owner')
            }

        } else {
            updateChats();
            alert("server not exist");
        }
    }
})

// styling textarea && chat
const textarea = document.getElementById("textarea");
function fixHeight() {
    let flag = false;
    let parent = textarea.parentNode;
    let rem = 1.2;
    let base = rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
    if (chat_list.scrollHeight - chat_list.offsetHeight == chat_list.scrollTop) {
        flag = true;
    }
    parent.style.height = '5rem';
    parent.style.height = (textarea.scrollHeight + base) + "px";
    chat_list.style.bottom = (textarea.scrollHeight + base) + "px";

    if (flag) {
        chat_list.scroll(chat_list.scrollWidth, chat_list.scrollHeight);
    }
}
textarea.addEventListener("input", () => {
    fixHeight();
    servers[selectedServer[0]].socket.emit('typing', { channel: selectedChannel[0] });
});

textarea.addEventListener('paste', function (e) {
    e.preventDefault()
    let text = e.clipboardData.getData('text/plain')
    for (let t of text.split("\n")) {
        e.target.append(t);
        e.target.append(document.createElement("br"));
    }
    e.target.removeChild(e.target.lastChild);
    e.target.append(text);
});

// submit msg or break line using enter key
textarea.addEventListener('keydown', function (e) {
    function _clear() {
        removeAllChildNodes(textarea);
        fixHeight();
    }

    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (textarea.innerHTML.replaceAll("<br>", "").replaceAll("&nbsp;", "").replaceAll(" ", "") == "") {
            _clear();
            return;
        }

        // let li = document.createElement("li");

        // let p = document.createElement('p');
        // p.append("KrisCris 10/12/2021 14:23 :");
        // li.append(p)

        // tool a
        // let a = document.createElement("a");
        // a.setAttribute("href", "#");
        let strArr = [];
        let textArr = textarea.innerHTML.split("<br>");
        for (let idx = 0; idx < textArr.length; idx++) {
            if (textArr[idx].replaceAll(" ", "").replaceAll("&nbsp;", "") == "" &&
                idx < textArr.length - 1 && textArr[idx + 1].replaceAll(" ", "").replaceAll("&nbsp;", "") == "") {
                continue;
            }
            strArr.push(textArr[idx]
                .replace(/&amp;/g, "&")
                .replace(/&lt;/g, "<")
                .replace(/&gt;/g, ">")
                .replace(/&quot;/g, "\"")
                .replace(/&#039;/g, "'")
                .replace(/&nbsp;/g, " ")
            );
        }

        // a.removeChild(a.lastChild);
        servers[selectedServer[0]].socket.emit("chat", {
            channel: selectedChannel[0],
            msg: strArr,
            attachment: null,
            token: localStorage.token,
            username: localStorage.username
        });

        // li.append(a);
        // chat_list.append(li);
        chat_list.scroll(chat_list.scrollWidth, chat_list.scrollHeight);
        e.target.textContent = "";
        _clear();
    } else if (e.key === 'Enter' && e.shiftKey) {

    }
})

function removeAllChildNodes(parent) {
    while (parent.firstChild) {
        parent.removeChild(parent.firstChild);
    }
}