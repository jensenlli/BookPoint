# from flask import Flask, request, jsonify
# import pandas as pd
# from sklearn.neighbors import NearestNeighbors
# import mysql.connector

# app = Flask(__name__)

# # Настройка подключения к базе данных
# def get_db_connection():
#     return mysql.connector.connect(
#         host="localhost",
#         user="your_username",
#         password="your_password",
#         database="your_database"
#     )

# # Получение названия книги по book_id
# def get_book_name_from_db(book_id):
#     conn = get_db_connection()
#     cursor = conn.cursor()
#     cursor.execute("SELECT name FROM book WHERE id = %s", (book_id,))
#     result = cursor.fetchone()
#     cursor.close()
#     conn.close()
#     return result[0] if result else None

# # Рекомендации на основе названия книги
# def recommend(book):
#     try:
#         query_index = pivot_table.index.get_loc(book)
#         distances, indices = model_knn.kneighbors(pt_matrix[query_index], n_neighbors=6)

#         recommendations = []
#         for i in range(1, len(distances.flatten())):
#             recommendations.append(pivot_table.index[indices.flatten()[i]])

#         return recommendations

#     except KeyError:
#         return find_similar_books(book)

# # Поиск похожих книг
# def find_similar_books(book):
#     similar_books = [b for b in pivot_table.index if book.lower() in b.lower()]
#     return similar_books[:10] if similar_books else []

# @app.route('/recommend/<int:book_id>', methods=['GET'])
# def get_recommendations(book_id):
#     book_name = get_book_name_from_db(book_id)

#     if book_name:
#         recommendations = recommend(book_name)
#         return jsonify(recommendations)
#     else:
#         return jsonify({"error": "Книга не найдена."}), 404

# if __name__ == "__main__":
#     app.run(debug=True)