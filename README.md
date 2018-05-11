Web chat room example
---------------------

### What the example has?

1. functionality of an RFC-6455 WebSockets server build by **[reactphp](https://reactphp.org/)**
2. a chat room logic server
3. a web page example build by [react](https://reactjs.org/)

### try
- run server
```shell
> git clone https://github.com/suntianxiang/websocket_chat_example.git
> cd websocket_chat_example/backend
> composer install
> cd ../frontend
> npm install | yarn install
> cd ../backend/server
> php http.php &
> php chat_room_server.php &
```
- visit http://localhost:8000/

### requires

1. php >= 7.0
