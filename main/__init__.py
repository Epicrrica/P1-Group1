#Centralized file for initializing modules
from flask import Flask

def create_app():
    app = Flask(__name__)
    app.config = None  # Placeholder for actual configuration
    return app