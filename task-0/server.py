#!/usr/bin/env python3
	
from http.server import BaseHTTPRequestHandler, HTTPServer	
HOST = "localhost"
	
PORT = 8080
	
 
	
class MyServer(BaseHTTPRequestHandler):
	
    def do_GET(self):
	
        query_string = self.path.split('?', 1)[-1]
        query_params = {}
        for param in query_string.split('&'):
            key, value = param.split('=')
            query_params[key] = value

        name = query_params.get('name', '')
        age = query_params.get('age', '')

        self.send_response(200)
	
        self.send_header("Content-type", "text/html; charset=utf-8")
	
        self.end_headers()
	
        self.wfile.write(bytes("<!doctype html><title>My Page</title>", "utf-8"))
        
        self.wfile.write(bytes(f"<p>{name} is {age} years old.</p>" , "utf-8"))	
        #with open("content.html", "rb") as stream:
        #    self.wfile.write(stream.read())


	
if __name__ == "__main__":        
	
    server = HTTPServer((HOST, PORT), MyServer)
	
    try:
	
        server.serve_forever()
	
    except KeyboardInterrupt:
	
        pass
	
    server.server_close()
