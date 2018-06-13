How to setup the program:

1. Install CouchDB

2. Install Apache2

3. Install PHP

4. Create the following tables (by going to localhost:5984/_utils):
    board
    messages
    users

5. Create the following views:
    board
    {
    	"views": {
    		"board": {
    			"map": "function (doc) {\n  emit(doc.category, {'id': doc.id, 'category': doc.category, 'title': doc.title, 'admins': doc.admin, 'users': doc.users});\n}"
    		}
    	}
    }
    
    messages
    {
    	"views": {
    		"messages": {
    			"map": "function (doc) {\n  emit(doc.to, {'type': doc.type, 'to': doc.to, 'from': doc.from, 'time': doc.time, 'data': doc.data, \"attachments\": doc._attachments});\n}"
    		}
    	}
    }
    
    users
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
    
6. Upload the contents of the frontend folder into your Apache folder "/var/www/html"


OR

Write the .img file to a SD card, and run it on a raspberry pi zero (http://kylemccaf.com/lemonaid/LEMON%20OS%206-12-18.img)
1. The ssh username is "couchdb" with a password of "new_password"
2. The localhost:5984/_utils username is "admin" with a password of "password"