# Main run file to start the application
from __init__ import create_app
from flask import render_template, request

app = create_app()

# ----------------------------
# Function Definitions
# ----------------------------


# ----------------------------
# Route
# ----------------------------

@app.route('/', methods=["GET", "POST"])
def index():
    return render_template("index.php")

if __name__ == "__main__":
    app.run(debug=True)