from bson.objectid import ObjectId
from flask import Blueprint, request
from flask_jwt_extended import jwt_required, get_jwt_identity

from util.util import reply, get_future_time, get_current_time

links = Blueprint(name='links', import_name=__name__, url_prefix='/links')


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
            maxLinks = result[0]['linksNum']
            # get current number of created links
            result = list(db.links.find({'uid': get_jwt_identity()}))
            if maxLinks - len(result) > 0:
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
            else:
                return reply(code=-2, msg="Reached Maximum Limit!"), 202

        return reply(code=-1, msg="User Not Exist, Please Try Again"), 404

    except Exception as ex:
        if hasattr(ex, 'code'):
            return reply(ex.code, msg=str(ex)), ex.code
        return reply(500, data={"err": str(ex)}), 500


@links.route('/', methods=['GET'])
@jwt_required()
def getAllLinks():
    try:
        from app import db
        result = list(db.links.find({'uid': get_jwt_identity()}))
        for link in result:
            del link['uid']
            del link['idx']
            link['_id'] = str(link['_id'])
            link['expired'] = True if get_current_time() > link['expiry'] else False
        return reply(1, data=result)

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
            if get_current_time() > result[0]['expiry']:
                return reply(-3), 202
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
        dbResp = db.links.delete_one({'_id': ObjectId(lid), 'uid': get_jwt_identity()})
        if dbResp.deleted_count == 1:
            return reply(1), 200

        return reply(-1), 404
    except Exception as ex:
        if hasattr(ex, 'code'):
            return reply(ex.code, msg=str(ex)), ex.code
        return reply(500, data={"err": str(ex)}), 500


@links.route('/<lid>', methods=['PATCH'])
@jwt_required()
def refresh_link(lid):
    try:
        from app import db
        new_link = request.form.get('newLink')
        if new_link:
            dbResp = db.links.update_one(
                {'_id': ObjectId(lid), 'uid': get_jwt_identity()},
                {'$set': {'original': new_link}}
            )
            if dbResp.modified_count:
                return reply(1, data={'newLink': new_link}), 200
        else:
            t = get_future_time(7)
            dbResp = db.links.update_one(
                {'_id': ObjectId(lid), 'uid': get_jwt_identity()},
                {'$set': {'expiry': t}}
            )
            if dbResp.modified_count:
                return reply(1, data={'newExpiry': t}), 200

        if dbResp.matched_count:
            return reply(1), 202
        return reply(-1), 404
    except Exception as ex:
        if hasattr(ex, 'code'):
            return reply(ex.code, msg=str(ex)), ex.code
        return reply(500, data={"err": str(ex)}), 500
