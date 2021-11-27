from bson.objectid import ObjectId
from flask import Blueprint, request
from flask_cors import CORS
from flask_jwt_extended import create_access_token, create_refresh_token, set_access_cookies, set_refresh_cookies, \
    jwt_required, get_jwt_identity, unset_jwt_cookies
from werkzeug.security import generate_password_hash, check_password_hash

from util.util import reply, gen_token

users = Blueprint(name="users", import_name=__name__, url_prefix='/users')
CORS(users)


@users.route('/register', defaults={'invitation': None}, methods=['POST'])
@users.route('/register/<invitation>', methods=['POST'])
def register(invitation):
    try:
        username = request.form["username"]
        password = request.form["password"]
        from app import db
        if not db.users.find({"username": username}).count():
            # hash password
            hashed_pw = generate_password_hash(password)

            # create user
            user = {
                "username": username,
                "password": hashed_pw,
                "inviteCode": gen_token(username),
                "linksNum": 5,
                "links": []
            }
            uid = str(db.users.insert_one(user).inserted_id)

            # create JWT
            response = reply(code=1)
            access_token = create_access_token(identity=uid)
            refresh_token = create_refresh_token(identity=uid)
            set_access_cookies(response, access_token)
            set_refresh_cookies(response, refresh_token)

            # process invitation
            if invitation is not None:
                # that user receive 5 more urls
                db.users.update_one(
                    {"inviteCode": invitation},
                    {"$inc": {"linksNum": 5}}
                )

            return response, 201
        else:
            return reply(-2), 202

    except Exception as ex:
        if hasattr(ex, 'code'):
            return reply(ex.code, msg=str(ex)), ex.code
        return reply(500, data={"err": str(ex)}), 500


@users.route("/login", methods=["POST"])
def login():
    try:
        username = request.form["username"]
        password = request.form["password"]
        from app import db
        result = list(db.users.find({"username": username}))
        if len(result):
            result = result[0]
            if check_password_hash(result['password'], password):
                # filter data
                # del result['password']
                # result['_id'] = str(result['_id'])
                uid = str(result['_id'])

                # resp
                response = reply(code=1)

                # auth token
                access_token = create_access_token(identity=uid)
                refresh_token = create_refresh_token(identity=uid)
                set_access_cookies(response, access_token)
                set_refresh_cookies(response, refresh_token)

                return response, 200

        return reply(code=0, msg="Wrong username or password!"), 401

    except Exception as ex:
        if hasattr(ex, 'code'):
            return reply(ex.code, msg=str(ex)), ex.code
        return reply(500, data={"err": str(ex)}), 500


@users.route("/logout", methods=["POST"])
def logout():
    response = reply(1)
    unset_jwt_cookies(response)
    return response


@users.route("/refresh", methods=["POST"])
@jwt_required(refresh=True)
def refresh():
    identity = get_jwt_identity()
    access_token = create_access_token(identity=identity)
    response = reply(1)
    set_access_cookies(response, access_token)
    return response, 200


@users.route('/', methods=["GET"])
@jwt_required()
def getInfo():
    try:
        objId = ObjectId(get_jwt_identity())
        from app import db
        result = list(db.users.find({'_id': objId}))
        if len(result):
            result = result[0]
            del result['password']
            del result['_id']
            # result['_id'] = str(result['_id'])

            return reply(code=1, data=result), 200

        return reply(code=-1), 404

    except Exception as ex:
        if hasattr(ex, 'code'):
            return reply(ex.code, msg=str(ex)), ex.code
        return reply(500, data={"err": str(ex)}), 500
