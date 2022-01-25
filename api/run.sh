#!/bin/sh
if [ ! -d "venv" ] 
then
    python3 -m venv venv
fi
. venv/bin/activate
pip install -r requirements.txt
export FLASK_APP=app.py
export FLASK_ENV=development
export FLASK_DEBUG=1
python -m flask run