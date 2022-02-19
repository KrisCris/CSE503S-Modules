
const months = {
    0: "January", 1: "February", 2: "March", 3: "April", 4: "May",
    5: "June", 6: "July", 7: "August", 8: "September", 9: "October", 10: "November", 11: "December"
};
const weekdays = {
    0: "Sunday", 1: "Monday", 2: "Tuesday", 3: "Wednesday", 4: "Thursday", 5: "Friday", 6: "Saturday"
}
const calendar = document.getElementsByClassName("calendar")[0];
const colLeft = document.getElementsByClassName("col left")[0];
const colMiddle = document.getElementsByClassName("col middle")[0];
const colRight = document.getElementsByClassName("col right")[0];
const days = document.getElementById("days");
const year = document.getElementById("year");
const month = document.getElementById("month");
const weekday = document.getElementById("weekday");

// nav in calender
const prev = document.getElementsByClassName("prev")[0];
const next = document.getElementsByClassName("next")[0];

prev.addEventListener("click", (event) => {
    let m = new Month(currentCal.getFullYear(), currentCal.getMonth());
    currentCal = m.prevMonth().getWeeks()[1].getDates()[1];
    drawCalender();
}, false);

next.addEventListener("click", (event) => {
    let m = new Month(currentCal.getFullYear(), currentCal.getMonth());
    currentCal = m.nextMonth().getWeeks()[1].getDates()[1];
    drawCalender();
}, false);

// Login Logout Register
const regBtn = document.getElementById("regBtn");
const loginBtn = document.getElementById("loginBtn");
const logoutBtn = document.getElementById("logoutBtn");
const username = document.getElementById("username");
const password = document.getElementById("password");
const loginDiv = document.getElementById("login");
const logoutDiv = document.getElementById("logout");
const hiMsg = document.getElementById("hi");

let currentCal = new Date();
let selectedDay = null;
let monthlyEvents = null;

regBtn.addEventListener('click', (event) => {
    let utxt = username.value;
    let pwtxt = password.value;
    let regex = /^[\w_\.\-]+$/;
    if (!regex.test(utxt) || pwtxt == '') {
        alert("Invalid Input");
    } else {
        post('/user/register.php', {
            username: utxt,
            password: pwtxt
        }).then(res => {
            if (res['code'] == 1) {
                localStorage.setItem('uid', res['data']['uid']);
                localStorage.setItem('token', res['data']['token']);
                localStorage.setItem('username', res['data']['username']);
                // do something after login
                initUserFunc();
            } else {
                alert("Register Failed!")
            }
        })
    }
}, false);

loginBtn.addEventListener('click', (event) => {
    let utxt = username.value;
    let pwtxt = password.value;
    let regex = /^[\w_\.\-]+$/;
    if (!regex.test(utxt) || pwtxt == '') {
        alert("Invalid Input!");
    } else {
        post('/user/login.php', {
            username: utxt,
            password: pwtxt
        }).then(res => {
            if (res['code'] == 1) {
                localStorage.setItem('uid', res['data']['uid']);
                localStorage.setItem('token', res['data']['token']);
                localStorage.setItem('username', res['data']['username']);
                // do something after login
                initUserFunc();
            } else {
                alert("Wrong Username or Password!")
            }
        })
    }
}, false);

logoutBtn.addEventListener('click', (event) => {
    post('/user/logout.php', {
        uid: localStorage.uid,
        token: localStorage.token
    }).then(res => {
        // clear localstorage
        localStorage.removeItem('uid');
        localStorage.removeItem('token');
        localStorage.removeItem('username');

        // do something after logout
        initGuestFunc()

        if (!res['code'] == 1) {
            alert("Invalid Access!");
        }
    })
}, false)

// after login
function showLogout() {
    removeAllChildNodes(hiMsg);
    hiMsg.append("Hi " + localStorage.username + "!");
    loginDiv.setAttribute("class", "displayNone");
    logoutDiv.removeAttribute("class");
}

// after logout
function showLogin() {
    loginDiv.removeAttribute("class");
    logoutDiv.setAttribute("class", "displayNone");
    document.getElementById("joinGroup").value = null;
    document.getElementById("createGroup").value = null;
    document.getElementById("groupCode").value = null;
    document.getElementById("sharedEvent").value = null;
}

const addEvent = document.getElementById("addEvent");
const eventList = document.getElementsByClassName("eventList")[0];
const tags = document.getElementById("tags");

// register init func
document.addEventListener("DOMContentLoaded", initCalender, false);

// init func
function initCalender() {
    // if obviously not login
    if (localStorage.token === undefined || localStorage.uid === undefined) {
        initGuestFunc();
    } else {
        // check login status
        // check if uid match $_SESSION["uid"] and token match token on server side
        post(
            "/user/isLogin.php", {
            uid: localStorage.uid,
            token: localStorage.token
        }).then(res => {
            if (res["code"] == 1) {
                initUserFunc();
            } else {
                initGuestFunc();
            }
        });
    }
}

// func for login user
function initUserFunc() {
    showLogout();
    addEvent.addEventListener("click", prepareAdd, false);
    submit.addEventListener("click", submitEvent, false);
    narrow();
    showTags();
    drawCalender()
}

// func for guests
function initGuestFunc() {
    localStorage.removeItem('uid');
    localStorage.removeItem('token');
    localStorage.removeItem('username');
    showLogin();
    addEvent.removeEventListener("click", prepareAdd, false);
    submit.removeEventListener("click", submitEvent, false)
    clearTags();
    drawCalender();
    narrow();
}

// render the calender
function drawCalender() {
    let currnetDate = new Date();
    let currentMonth = new Month(currentCal.getFullYear(), currentCal.getMonth());
    let weeks = currentMonth.getWeeks();
    let beginTS = parseInt((weeks[0].getDates()[0].getTime()) / 1000);
    let endTS = parseInt((weeks[weeks.length - 1].nextWeek().getDates()[0].getTime() - 1) / 1000);

    // fix timezone switch e.g. CDT -> CST
    let offset = weeks[0].getDates()[0].getTimezoneOffset();
    let endTS2 = 0;
    let beginTS2 = 0;
    breakFlag:
    for (let week of weeks) {
        for (let date of week.getDates()) {
            if (date.getTimezoneOffset() != offset) {
                beginTS2 = parseInt(date.getTime() / 1000);
                endTS2 = parseInt(date.getTime() / 1000) - 1;
                break breakFlag;
            }
        }
    }

    // try query data
    if (localStorage.token === undefined || localStorage.uid === undefined) {
        // draw calender by weeks
        removeAllChildNodes(days);
        for (let week of weeks) {
            for (let date of week.getDates()) {
                let li = document.createElement('li');
                let a = document.createElement('a');
                a.append(date.getDate());
                a.setAttribute("href", "#");
                a.setAttribute("weekday", weekdays[date.getDay()]);
                a.setAttribute("timestamp", parseInt(date.getTime() / 1000))

                if (
                    currnetDate.getDate() == date.getDate() &&
                    currnetDate.getMonth() == date.getMonth() &&
                    currnetDate.getFullYear() == date.getFullYear()
                ) {
                    a.classList.add("today");
                }
                li.append(a);
                days.append(li);

                // update selected date
                if (selectedDay != null && selectedDay.getAttribute("timestamp") >= beginTS && selectedDay.getAttribute("timestamp") <= endTS) {
                    if (selectedDay.getAttribute("timestamp") == a.getAttribute("timestamp")) {
                        setSelectedDate(a, false);
                    }
                } else {
                    setSelectedDate(a, false);
                }

                // register click event for each date
                a.addEventListener("click", (event) => {
                    setSelectedDate(a, false);
                }, false);
            }
        }
        // update year and month
        removeAllChildNodes(year);
        year.append(currentCal.getFullYear());
        removeAllChildNodes(month);
        month.append(months[currentCal.getMonth()]);
    } else {
        // fetch events
        post('/event/getMonthlyEvents.php', {
            uid: localStorage.uid,
            token: localStorage.token,
            beginTS: beginTS,
            endTS: endTS,
            beginTS2: beginTS2,
            endTS2: endTS2
        }).then(res => {
            if (res['code'] == 1) {
                // store events of this month;
                monthlyEvents = res['data'];
                let activeTags = getActivatedTags();

                // draw calender
                removeAllChildNodes(days);
                for (let week of weeks) {
                    for (let date of week.getDates()) {
                        let li = document.createElement('li');
                        let a = document.createElement('a');
                        a.append(date.getDate());
                        a.setAttribute("href", "#");
                        a.setAttribute("weekday", weekdays[date.getDay()]);
                        a.setAttribute("timestamp", parseInt(date.getTime() / 1000))

                        // show if has event
                        if (monthlyEvents[a.getAttribute("timestamp")].length) {
                            // disable some based on tags
                            for (let event of monthlyEvents[a.getAttribute("timestamp")]) {
                                if (activeTags.includes(event['cid'])) {
                                    // has event
                                    a.classList.add("event");
                                }
                            }
                        }
                        // if it is today
                        if (
                            currnetDate.getDate() == date.getDate() &&
                            currnetDate.getMonth() == date.getMonth() &&
                            currnetDate.getFullYear() == date.getFullYear()
                        ) {
                            a.classList.add("today");
                        }
                        li.append(a);
                        days.append(li);

                        // update selected date
                        if (selectedDay != null && selectedDay.getAttribute("timestamp") >= beginTS && selectedDay.getAttribute("timestamp") <= endTS) {
                            if (selectedDay.getAttribute("timestamp") == a.getAttribute("timestamp")) {
                                setSelectedDate(a, true);
                            }
                        } else {
                            setSelectedDate(a, true);
                        }

                        // register click event for each date
                        a.addEventListener("click", (event) => {
                            setSelectedDate(a, true);
                        }, false);

                    }
                }
                removeAllChildNodes(year);
                year.append(currentCal.getFullYear());
                removeAllChildNodes(month);
                month.append(months[currentCal.getMonth()]);
            } else {
                // not login, force logout
                initGuestFunc();
                alert("Invalid user credential!");
            }
        })
    }
}

function clearTags() {
    removeAllChildNodes(tags);
}

function showTags() {
    post("/category/getCates.php", {
        uid: localStorage.uid,
        token: localStorage.token
    }).then(res => {
        if (res["code"] == 1) {
            removeAllChildNodes(tags);
            // default choice
            let h3 = document.createElement('h3');
            let noneTag = document.createElement("a");
            noneTag.setAttribute("href", "#");
            noneTag.append("NoneTag");
            noneTag.setAttribute("cid", 0);

            // enable / disable a tag
            noneTag.setAttribute("activated", "true");
            noneTag.addEventListener("click", (event) => {
                if (event.target.getAttribute("activated") == "true") {
                    event.target.setAttribute("activated", "false");
                } else {
                    event.target.setAttribute("activated", "true");
                }
                drawCalender();
            }, false);

            h3.append(noneTag);
            tags.append(h3);


            for (let c of res["data"]) {
                let h3 = document.createElement('h3');
                let tag = document.createElement("a");
                tag.setAttribute("href", "#");
                tag.append(c["name"]);
                tag.setAttribute("cid", c["id"]);

                // enable / disable a tag
                tag.setAttribute("activated", "true");
                tag.addEventListener("click", (event) => {
                    if (event.target.getAttribute("activated") == "true") {
                        event.target.setAttribute("activated", "false");
                    } else {
                        event.target.setAttribute("activated", "true");
                    }
                    drawCalender();
                }, false);

                h3.append(tag);
                tags.append(h3);
            }
        } else if (res["code"] == 0) {
            initGuestFunc();
            alert("Invalid user credential!");
        } else {
            alert("Something wrong, please refresh the page");
        }
    })
}

function getActivatedTags() {
    let arr = [];
    for (let i = 0; i < tags.children.length; i++) {
        if (tags.children[i].children[0].getAttribute("activated") == 'true') {
            arr[arr.length] = tags.children[i].children[0].getAttribute("cid") == 0 ? null : parseInt(tags.children[i].children[0].getAttribute("cid"));
        }
    }
    return arr;
}

function setSelectedDate(date, isLogin) {
    // set selected date class
    date.classList.add("selected")

    if (selectedDay != null) {
        // remove old selected date's 'selected' class
        if (selectedDay === date) {
            ;
        } else {
            selectedDay.classList.remove("selected");
        }
    }
    selectedDay = date;

    // update event bar
    removeAllChildNodes(weekday);
    weekday.append(selectedDay.getAttribute("weekday"));
    removeAllChildNodes(eventList);
    if (!isLogin) {
        let noticeEvent = document.createElement("li");
        noticeEvent.append("Login to access full features!");
        eventList.append(noticeEvent);
    } else {
        // if we successfully got event lists
        if (monthlyEvents) {
            let activeTags = getActivatedTags();
            let ts = date.getAttribute("timestamp");
            let dailyEvents = monthlyEvents[ts];
            let count = 0;
            for (let event of dailyEvents) {
                // skip events from disabled tags
                if (!activeTags.includes(event["cid"])) {
                    continue;
                }
                let e = document.createElement("li");
                e.append(event["title"]);
                e.addEventListener("click", function () {
                    prepareEdit(event["id"]);
                }, false);
                e.setAttribute("isEvent", true);
                eventList.append(e);
                count++;
            }
            // no event on that day...
            if (count == 0) {
                let noticeEvent = document.createElement("li");
                noticeEvent.append("Today has no event from selected tags!");
                eventList.append(noticeEvent);
            }
        } else {
            let noticeEvent = document.createElement("li");
            noticeEvent.append("Something went wrong, please refresh page!");
            eventList.append(noticeEvent);
        }
    }
    narrow();
}

// operations related to events
const cateSelect = document.getElementById("categories");
const grpSelect = document.getElementById("groups");
const dateBegin = document.getElementById("date-begin");
const dateEnd = document.getElementById("date-end");
const submit = document.getElementById("submit");
const title = document.getElementById("title");
const detail = document.getElementById("detail");
const newTag = document.getElementById("newTag");

const edit = document.getElementById("edit");
const del = document.getElementById("del");
const share = document.getElementById("share");
const displayShareToken = document.getElementById("shareToken");

// delete
del.addEventListener("click", (event) => {
    post('/event/delEvent.php', {
        uid: localStorage.uid,
        token: localStorage.token,
        eid: del.getAttribute("eid")
    }).then(res => {
        if (res['code'] == 1) {
            initUserFunc();
        } else if (res["code"] == 0) {
            initGuestFunc();
            alert("Invalid user credential!");
        } else {
            alert(res['msg']);
        }
    })
}, false);

// share
share.addEventListener("click", (event) => {
    post('/event/shareEvent.php', {
        uid: localStorage.uid,
        token: localStorage.token,
        eid: share.getAttribute("eid")
    }).then(res => {
        if (res['code'] == 1) {
            removeAllChildNodes(displayShareToken);
            displayShareToken.append(res['data']['shareToken']);
        } else if (res["code"] == 0) {
            initGuestFunc();
            alert("Invalid user credential!");
        } else {
            alert(res['msg']);
        }
    })
}, false);

// edit
edit.addEventListener("click", (event) => {
    // process inputs
    if (isNaN(dateBegin.valueAsNumber) || isNaN(dateEnd.valueAsNumber)) {
        alert("Invalid Time!")
        return;
    }
    let startTime = getPickerTime(dateBegin);
    let endTime = getPickerTime(dateEnd);
    let currentTime = parseInt(new Date().getTime() / 1000)
    if (endTime < startTime || startTime < currentTime) {
        alert("Invalid Time!")
        return;
    }
    let titleTxt = title.value.trim();
    let detailTxt = detail.value.trim();

    if (titleTxt == "") {
        alert("Write something for event title!")
        return;
    }

    let cid = 0;
    let gid = 0;
    for (let c of cateSelect.children) {
        if (c.selected) {
            cid = c.value;
            break;
        }
    }
    for (let g of grpSelect.children) {
        if (g.selected) {
            gid = g.value;
            break;
        }
    }

    let newTagTxt = newTag.value.trim();

    // request edit
    post('/event/editEvent.php', {
        uid: localStorage.uid,
        token: localStorage.token,
        eid: edit.getAttribute("eid"),
        cid: cid,
        gid: gid,
        title: titleTxt,
        detail: detailTxt,
        isFullDay: 0,
        start: startTime,
        end: endTime,
        newTag: newTagTxt
    }).then(res => {
        if (res['code'] == 1) {
            initUserFunc();
        } else if (res["code"] == 0) {
            initGuestFunc();
            alert("Invalid user credential!");
        } else {
            alert(res['msg']);
        }
    })
}, false);

// some necessary steps before open edit panel
function prepareEdit(eid) {
    // clean
    clearInputs();
    edit.classList.remove("displayNone");
    del.classList.remove("displayNone");
    share.classList.remove("displayNone");

    // get event data
    post("/event/getEvent.php", {
        uid: localStorage.uid,
        token: localStorage.token,
        eid: eid
    }).then(res => {
        if (res["code"] == 1) {
            let data = res["data"];
            // prevent modifying group events that belong to others
            if (data["uid"] != localStorage.uid) {
                edit.classList.add("displayNone");
                del.classList.add("displayNone");
                share.classList.add("displayNone");
            }
            // map value to editor
            title.value = data["title"];
            detail.value = data["detail"];

            let min = timestampToISOLocalTime();

            let oldBegin = timestampToISOLocalTime(data["start"] * 1000);
            let oldEnd = timestampToISOLocalTime(data["end"] * 1000);

            dateBegin.setAttribute("min", min);
            dateEnd.setAttribute("min", min);
            dateBegin.value = oldBegin;
            dateEnd.value = oldEnd;


            edit.setAttribute("eid", eid);
            del.setAttribute("eid", eid);
            share.setAttribute("eid", eid);

            // get all available group/tag choices
            post("/category/getCates.php", {
                uid: localStorage.uid,
                token: localStorage.token
            }).then(res => {
                if (res["code"] == 1) {
                    removeAllChildNodes(cateSelect);
                    // default choice
                    let defaultOpt = document.createElement("option");
                    defaultOpt.setAttribute("value", 0);
                    defaultOpt.append("No Category");
                    cateSelect.append(defaultOpt);
                    // set current choice
                    if (data["cid"] == null) {
                        defaultOpt.selected = true;
                    }

                    for (let c of res["data"]) {
                        let choice = document.createElement("option");
                        choice.setAttribute("value", c["id"]);
                        choice.append(c["name"]);
                        cateSelect.append(choice);
                        // set current choice
                        if (data["cid"] && data["cid"] == c["id"]) {
                            choice.selected = true;
                        }
                    }

                    post("/group/getGroups.php", {
                        uid: localStorage.uid,
                        token: localStorage.token
                    }).then(res => {
                        if (res['code'] == 1) {
                            removeAllChildNodes(grpSelect);
                            let defaultOpt = document.createElement("option");
                            defaultOpt.setAttribute("value", 0);
                            defaultOpt.append("No Group");
                            grpSelect.append(defaultOpt);
                            // set current choice
                            if (data["gid"] == null) {
                                defaultOpt.selected = true;
                            }

                            for (let g of res["data"]) {
                                let choice = document.createElement("option");
                                choice.setAttribute("value", g["gid"]);
                                choice.append(g["name"]);
                                grpSelect.append(choice);
                                // set current choice
                                if (data["gid"] && data["gid"] == g["gid"]) {
                                    choice.selected = true;
                                }
                            }
                        } else {
                            alert(res["msg"]);
                        }
                    })
                } else {
                    alert(res["msg"]);
                }
            })
            // end of getting tags/ groups
        } else if (res["code"] == 0) {
            initGuestFunc();
            alert("Invalid user credential!");
        } else {
            alert("Something wrong, please refresh the page");
        }
    })
    // show and unshow some btns
    document.getElementById("exist").classList.remove("displayNone");
    document.getElementById("new").classList.add("displayNone");
    wide();
}

// same to above
function prepareAdd() {
    clearInputs();
    post("/category/getCates.php", {
        uid: localStorage.uid,
        token: localStorage.token
    }).then(res => {
        if (res["code"] == 1) {
            removeAllChildNodes(cateSelect);
            // default choice
            let defaultOpt = document.createElement("option");
            defaultOpt.setAttribute("value", 0);
            defaultOpt.append("No Category");
            cateSelect.append(defaultOpt);

            for (let c of res["data"]) {
                let choice = document.createElement("option");
                choice.setAttribute("value", c["id"]);
                choice.append(c["name"]);
                cateSelect.append(choice);
            }

            post("/group/getGroups.php", {
                uid: localStorage.uid,
                token: localStorage.token
            }).then(res => {
                if (res['code'] == 1) {
                    removeAllChildNodes(grpSelect);
                    let defaultOpt = document.createElement("option");
                    defaultOpt.setAttribute("value", 0);
                    defaultOpt.append("No Group");
                    grpSelect.append(defaultOpt);

                    for (let g of res["data"]) {
                        let choice = document.createElement("option");
                        choice.setAttribute("value", g["gid"]);
                        choice.append(g["name"]);
                        grpSelect.append(choice);
                    }
                }
            })
        } else if (res["code"] == 0) {
            initGuestFunc();
            alert("Invalid user credential!");
        } else {
            alert("Something wrong, please refresh the page");
        }
    })

    dft = timestampToISOLocalTime(selectedDay.getAttribute("timestamp") * 1000);
    min = timestampToISOLocalTime()

    dateBegin.setAttribute("min", min);
    dateBegin.value = dft;
    dateEnd.setAttribute("min", min);
    dateEnd.value = dft;

    document.getElementById("new").classList.remove("displayNone");
    document.getElementById("exist").classList.add("displayNone");
    wide();
}

// add an event
function submitEvent() {
    // filter input
    if (isNaN(dateBegin.valueAsNumber) || isNaN(dateEnd.valueAsNumber)) {
        alert("Invalid Time!")
        return;
    }
    let startTime = getPickerTime(dateBegin);
    let endTime = getPickerTime(dateEnd);
    let currentTime = parseInt(new Date().getTime() / 1000)
    if (endTime < startTime || startTime < currentTime) {
        alert("Invalid Time!")
        return;
    }
    let titleTxt = title.value.trim();
    let detailTxt = detail.value.trim();

    if (titleTxt == "") {
        alert("Write something for event title!")
        return;
    }

    let cid = 0;
    let gid = 0;
    for (let c of cateSelect.children) {
        if (c.selected) {
            cid = c.value;
            break;
        }
    }
    for (let g of grpSelect.children) {
        if (g.selected) {
            gid = g.value;
            break;
        }
    }

    let newTagTxt = newTag.value.trim();

    let data = {
        uid: localStorage.uid,
        token: localStorage.token,
        cid: cid,
        gid: gid,
        title: titleTxt,
        detail: detailTxt,
        isFullDay: 0,
        start: startTime,
        end: endTime,
        newTag: newTagTxt
    };

    post("/event/addEvent.php", data).then(res => {
        if (res["code"] == 1) {
            clearInputs();
            initUserFunc();
        } else if (res["code"] == 0) {
            initGuestFunc();
            alert("Invalid user credential!");
        } else {
            alert("Something wrong, please refresh the page");
        }
    });
}

// some helper functions

function clearInputs() {
    title.value = null;
    detail.value = null;
    newTag.value = null;
    removeAllChildNodes(displayShareToken);
}

function getPickerTime(picker) {
    let t = new Date(picker.value);
    return parseInt(t.getTime() / 1000);
}

function timestampToISOLocalTime(timestamp) {
    let t = new Date(new Date().toString().split('GMT')[0] + ' UTC').toISOString();
    if (timestamp) {
        t = new Date(new Date(timestamp).toString().split('GMT')[0] + ' UTC').toISOString();
    }
    t = t.substr(0, t.length - 1);
    return t;
}

function removeAllChildNodes(parent) {
    while (parent.firstChild) {
        parent.removeChild(parent.firstChild);
    }
}

function removeAllListener(element) {
    let clone = element.cloneNode(true);
    element.parentNode.replaceChild(clone, element);
}

function wide() {
    calendar.setAttribute("class", "calendar2");
    colLeft.setAttribute("class", "col left2");
    colMiddle.setAttribute("class", "col middle2");
    colRight.setAttribute("class", "col right2");
}

function narrow() {
    calendar.setAttribute("class", "calendar");
    colLeft.setAttribute("class", "col left");
    colMiddle.setAttribute("class", "col middle");
    colRight.setAttribute("class", "col right");
}

// extra credit
function joinGroup() {
    let uuid = document.getElementById("joinGroup").value;
    let regex = /^[\w_\.\-]+$/;
    if (!regex.test(uuid)) {
        alert("Invalid Input!");
    } else {
        post("/group/joinGroup.php", {
            uid: localStorage.uid,
            token: localStorage.token,
            uuid: uuid
        }).then(res => {
            if (res["code"]) {
                initUserFunc();
                alert("Joined!");
            } else if (res["code"] == 0) {
                initGuestFunc();
                alert("Invalid user credential!")
            } else {
                alert("Error, please refresh webpage!");
            }
        })
    }

}

function createGroup() {
    let groupName = document.getElementById("createGroup").value;
    let regex = /^[\w_\.\-]+$/;
    if (!regex.test(groupName)) {
        alert("Invalid Input!");
    } else {
        post("/group/addGroup.php", {
            uid: localStorage.uid,
            token: localStorage.token,
            name: groupName
        }).then(res => {
            if (res["code"]) {
                alert("Group " + name + " created!");
                removeAllChildNodes(document.getElementById("groupCode"));
                document.getElementById("groupCode").append(res["data"]["uuid"]);
            } else if (res["code"] == 0) {
                initGuestFunc();
                alert("Invalid user credential!")
            } else {
                alert("Error, please refresh webpage!");
            }
        })
    }
}

function addSharedEvent() {
    let shareTokenTxt = document.getElementById("sharedEvent").value;
    let regex = /^[\w_\.\-]+$/;
    if (!regex.test(shareTokenTxt)) {
        alert("Invalid Input!");
    } else {
        post("/event/addShare.php", {
            uid: localStorage.uid,
            token: localStorage.token,
            shareToken: shareTokenTxt
        }).then(res => {
            if (res["code"]) {
                initUserFunc();
                alert("Added!");
            } else if (res["code"] == 0) {
                initGuestFunc();
                alert("Invalid user credential!")
            } else {
                alert("Error, please refresh webpage!");
            }
        })
    }
}
