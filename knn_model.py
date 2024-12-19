import pandas as pd
import numpy as np
import pymysql
from sklearn.neighbors import NearestNeighbors

class KNNRecommender:
    def __init__(self, db_config):
        self.db_config = db_config
        self.user_ratings = None
        self.model = None

    def load_data(self):
        # Подключение к базе данных с использованием контекстного менеджера
        try:
            with pymysql.connect(**self.db_config) as connection:
                query = "SELECT user_id, book_id, rating_user FROM favorites"
                
                # Используем курсор для выполнения запроса
                with connection.cursor() as cursor:
                    cursor.execute(query)
                    # Получаем все строки результата
                    results = cursor.fetchall()  # Получаем все результаты запроса


                # Загрузка данных в DataFrame
                self.user_ratings = pd.DataFrame(results, columns=['user_id', 'book_id', 'rating_user'])
                print(self.user_ratings)


            # Проверка, что данные загружены
            if self.user_ratings.empty:
                raise ValueError("Загруженные данные пусты. Проверьте запрос к базе данных.")

            # Преобразование rating_user в числовой тип
            self.user_ratings['rating_user'] = pd.to_numeric(self.user_ratings['rating_user'], errors='coerce')

            # Очистка данных: удаление строк с NaN значениями в rating_user
            self.user_ratings.dropna(subset=['rating_user'], inplace=True)

            # Использование pivot_table для обработки дубликатов
            self.user_ratings = self.user_ratings.pivot_table(
                index='book_id',
                columns='user_id',
                values='rating_user',
                fill_value=0
            )

            # Проверка, что данные после обработки не пустые
            if self.user_ratings.empty:
                raise ValueError("Данные после обработки пусты. Проверьте данные в базе.")

            print("Данные успешно загружены и обработаны:")
            print(self.user_ratings.head())

        except Exception as e:
            print(f"Произошла ошибка при загрузке данных: {e}")

    def train_model(self):
        # Проверка, загружены ли данные
        if self.user_ratings is None:
            raise ValueError("Данные не загружены. Сначала загрузите данные с помощью load_data().")

        # Обучение модели KNN
        try:
            # Обратите внимание на .T, чтобы поменять местами строки и столбцы
            self.model = NearestNeighbors(n_neighbors=15, algorithm='auto', metric='cosine')
            self.model.fit(self.user_ratings.values.T)  # Обучаем на пользователях
            print("Модель успешно обучена.")
        except Exception as e:
            print(f"Произошла ошибка при обучении модели: {e}")

    def get_recommendations(self, user_id, n):
        # Логика получения рекомендаций на основе user_id
        if self.model is None:
            raise ValueError("Модель не обучена. Сначала вызовите train_model().")

        # Получение индекса пользователя
        if user_id not in self.user_ratings.columns:
            raise ValueError(f"Пользователь {user_id} не найден в данных.")

        user_index = list(self.user_ratings.columns).index(user_id)

        # Получение рекомендаций
        distances, indices = self.model.kneighbors(self.user_ratings.iloc[:, user_index].values.reshape(1, -1), n_neighbors=n + 1)
        
        # Словарь для хранения оценок книг
        book_scores = {}

        # Получаем книги, которые пользователь уже оценил
        user_rated_books = set(self.user_ratings[user_id][self.user_ratings[user_id] > 0].index)

        # Проходим по ближайшим соседям
        for i in range(1, n + 1):  # начиная с 1, чтобы пропустить самого пользователя
            neighbor_index = indices.flatten()[i]
            neighbor_user_id = self.user_ratings.columns[neighbor_index]

            # Получаем книги, оцененные соседом
            neighbor_ratings = self.user_ratings[neighbor_user_id]
            for book_id, rating in neighbor_ratings.items():
                if rating > 0 and book_id not in user_rated_books:  # только положительные оценки и книги, которые не оценены пользователем
                    if book_id not in book_scores:
                        book_scores[book_id] = []
                    book_scores[book_id].append(rating)

        # Получаем средние оценки для каждой книги
        average_scores = {book_id: np.mean(scores) for book_id, scores in book_scores.items()}

        # Сортируем книги по среднему рейтингу и выбираем n лучших
        recommended_books = sorted(average_scores, key=average_scores.get, reverse=True)[:n]

        print(f"Рекомендации для пользователя {user_id}: {recommended_books}")
        return recommended_books
