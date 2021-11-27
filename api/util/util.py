from flask import jsonify
import uuid

REPLY_CODES = {
    500: 'Error',
    -2: 'Already Exist',
    -1: 'Not Exist',
    0: 'Login Required',
    1: 'Success',
}


def reply(code, msg=None, data=None):
    if data is None:
        data = []
    if code in REPLY_CODES:
        return jsonify({
            'code': code,
            'msg': REPLY_CODES[code] if msg is None else msg,
            'data': data
        })

    return jsonify({
        'code': 500,
        'msg': 'Unknown Error',
        'data': data
    })


def gen_token(key):
    time_uuid = uuid.uuid1()
    return str(uuid.uuid5(time_uuid, key))