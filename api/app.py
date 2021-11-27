import configparser
import os
from datetime import timedelta, datetime, timezone

import pymongo
from flask import Flask
from flask_cors import CORS
from flask_jwt_extended import JWTManager, get_jwt_identity, create_access_token, get_jwt, set_access_cookies

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

CORS(app)
jwt = JWTManager(app)


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


if __name__ == '__main__':
    app.run()
