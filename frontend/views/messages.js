{
	"views": {
		"messages": {
			"map": "function (doc) {\n  emit(doc.to, {'type': doc.type, 'to': doc.to, 'from': doc.from, 'time': doc.time, 'data': doc.data, \"attachments\": doc._attachments});\n}"
		}
	}
}