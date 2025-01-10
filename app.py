from flask import Flask, request, jsonify
import pandas as pd
from sklearn.neighbors import NearestNeighbors
import pymysql.cursors
from knn_model import KNNRecommender

app = Flask(__name__)

# Конфигурация подключения к базе данных
db_config = {
    'host': '127.0.0.1',
    'port': 3306,
    'user': 'root',
    'password': '',
    'database': 'digitalbook',
    'cursorclass': pymysql.cursors.DictCursor  
}

# Инициализация рекомендателя
recommender = KNNRecommender(db_config)

@app.route('/recommend', methods=['GET'])
def recommend():
    user_id = request.args.get('user_id', type=int)
    n_recommendations = request.args.get('n', default=5, type=int)

    if user_id is None:
        return jsonify({"error": "User  ID is required"}), 400
    
    recommender.load_data()
    recommender.train_model()

    recommendations = recommender.get_recommendations(user_id, n_recommendations)

    return jsonify({"recommendations": recommendations})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
