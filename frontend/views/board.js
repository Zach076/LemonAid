{
	"views": {
		"board": {
			"map": "function (doc) {\n  emit(doc.category, {'id': doc.id, 'category': doc.category, 'title': doc.title, 'admins': doc.admin, 'users': doc.users});\n}"
		}
	}
}