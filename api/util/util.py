import time
import uuid

from flask import jsonify

REPLY_CODES = {
    500: 'Error',
    -3: 'Expired',
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
        'code': code,
        'msg': msg,
        'data': data
    })


def gen_token(key):
    time_uuid = uuid.uuid1()
    return str(uuid.uuid5(time_uuid, key))


def get_current_time():
    return int(time.time())


def get_time_gap(old):
    return int(time.time()) - old


def get_future_time(days, now=None):
    return int((time.time() if now is None else now) + 3600 * 24 * days)
