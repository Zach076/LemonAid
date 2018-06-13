{
  "views": {
    "user": {
      "map": "function (doc) {\n  emit(doc.Username, {'email': doc.Email, 'password': doc.Password, 'AccessLevel': doc.AccessLevel});\n}"
    },
    "email": {
      "map": "function (doc) {\n  emit(doc.Email);\n}"
    },
    "username": {
      "map": "function (doc) {\n  emit(doc.Username);\n}"
    }
  }
}