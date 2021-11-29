import configparser
import os
from datetime import timedelta, datetime, timezone
from util.util import reply
import pymongo
from flask import Flask
from flask_cors import CORS
from flask_jwt_extended import JWTManager, get_jwt_identity, create_access_token, get_jwt, set_access_cookies
from hashids import Hashids

from api.links import links
from api.users import users

try:
    mongo = pymongo.MongoClient(
        host="127.0.0.1",
        port=27017,
        serverSelectionTimeoutMS=1000
    )
    db = mongo.shortUrl
    mongo.server_info()

    # init
    if 'counters' not in db.list_collection_names():
        seq = {
            '_id': 'urlId',
            'seq': 0
        }
        db.counters.insert(seq)

except Exception as ex:
    print(f"Error connecting to DB: {ex}")

config = configparser.ConfigParser()
config.read(os.path.abspath(os.path.join(".ini")))

app = Flask(__name__)
app.register_blueprint(links)
app.register_blueprint(users)
app.config['DEBUG'] = config['DEV']['DEBUG']
app.config['JWT_SECRET_KEY'] = config['DEV']['JWT_SECRET']
app.config["JWT_TOKEN_LOCATION"] = ["cookies"]
app.config["JWT_ACCESS_TOKEN_EXPIRES"] = timedelta(hours=24)

CORS(app, supports_credentials=True)
jwt = JWTManager(app)

hashids = Hashids(min_length=4, salt=app.config['JWT_SECRET_KEY'])


@app.after_request
def refresh_expiring_jwts(response):
    try:
        exp_timestamp = get_jwt()["exp"]
        now = datetime.now(timezone.utc)
        target_timestamp = datetime.timestamp(now + timedelta(hours=12))
        if target_timestamp > exp_timestamp:
            access_token = create_access_token(identity=get_jwt_identity())
            set_access_cookies(response, access_token)
        return response
    except (RuntimeError, KeyError):
        # Case where there is not a valid JWT. Just return the original respone
        return response


@jwt.invalid_token_loader
def invalid_token_resp(header, payload):
    return reply(-1, 'invalid token')


@jwt.expired_token_loader
def expired_token_reps(header, payload):
    return reply(-3, 'expired token')


@jwt.needs_fresh_token_loader
def expired_token_reps(header, payload):
    return reply(-3, 'expired token')


@jwt.revoked_token_loader
def revoked_token_loader(header, payload):
    return reply(-1, 'token revoked')


@jwt.unauthorized_loader
def unauthorized_loader(reason):
    return reply(-1, reason)


@jwt.token_verification_failed_loader
def token_verification_failed_loader(header, payload):
    return reply(-1, 'token verification failed')


if __name__ == '__main__':
    app.run()
