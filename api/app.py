from flask import Flask

app = Flask(__name__)


@app.route('/<id>')
def hello_world(id):  # put application's code here
    return id


if __name__ == '__main__':
    app.run()
