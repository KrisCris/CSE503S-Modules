class Message{
    type;
    msg;
    constructor(type, msg){
        this.msg = msg;
        this.type = type;
    }

    getMsg(){
        return this.msg;
    }

    static build(jsonObj){
        return new Message(jsonObj['type'], jsonObj['msg']);
    }
}

module.exports = { Message }