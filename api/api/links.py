from bson.objectid import ObjectId
from flask import Blueprint, request
from flask_cors import CORS
from flask_jwt_extended import jwt_required, get_jwt_identity

from util.util import reply, get_future_time

links = Blueprint(name='links', import_name=__name__, url_prefix='/links')
CORS(links)


@links.route('/', methods=['PUT'])
@jwt_required()
def add_link():
    from app import db, hashids

    def _getNextSEQ():
        db.counters.update(
            {'_id': 'urlId'},
            {'$inc': {'seq': 1}},
            upsert=True
        )
        return list(db.counters.find({'_id': 'urlId'}))[0]['seq']

    try:
        objId = ObjectId(get_jwt_identity())

        result = list(db.users.find({'_id': objId}))
        if len(result):
            idx = _getNextSEQ()
            shortened = hashids.encode(idx)
            link = {
                'uid': f'{objId}',
                'idx': idx,
                'original': request.form['link'],
                'shortened': shortened,
                'expiry': get_future_time(7)
            }
            lid = str(db.links.insert_one(link).inserted_id)
            del link['uid']
            del link['_id']
            link['id'] = lid
            return reply(code=1, data=link), 200

        return reply(code=-1), 404

    except Exception as ex:
        if hasattr(ex, 'code'):
            return reply(ex.code, msg=str(ex)), ex.code
        return reply(500, data={"err": str(ex)}), 500


@links.route('/<shortened>', methods=['GET'])
def parse_link(shortened):
    try:
        from app import db, hashids
        decoded = hashids.decode(shortened)
        idx = decoded[0] if len(decoded) else None
        result = list(db.links.find({'idx': idx}))
        if len(result):
            return reply(code=1, data={'link': result[0]['original']}), 200

        return reply(-1), 404
    except Exception as ex:
        if hasattr(ex, 'code'):
            return reply(ex.code, msg=str(ex)), ex.code
        return reply(500, data={"err": str(ex)}), 500


@links.route('/<lid>', methods=['DELETE'])
@jwt_required()
def delete_link(lid):
    try:
        from app import db
        dbResp = db.links.delete_one({'_id': ObjectId(lid)})
        if dbResp.deleted_count == 1:
            return reply(1), 200

        return reply(-1), 404
    except Exception as ex:
        if hasattr(ex, 'code'):
            return reply(ex.code, msg=str(ex)), ex.code
        return reply(500, data={"err": str(ex)}), 500
