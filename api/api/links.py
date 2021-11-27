from flask import Blueprint, request, jsonify
from flask_cors import CORS

links = Blueprint(name='links',import_name=__name__,url_prefix='/links')
CORS(links)

@links.route('/<uid>/', methods=['POST'])
def add_link():
    return jsonify({'code':1})