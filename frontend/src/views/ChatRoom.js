import React from 'react';
import ChatInput from '../components/ChatInput.js';
import './ChatRoom.css';
import webSocket from '../api/webSocket.js';

// chat message item
const MessageItem = function (props) {
  return (
    <li>
      <div className="message-item">
        <div className="username">
          {props.userName}
        </div>
        <div className="content">
          <div className="text">
            <pre>
              {props.content}
            </pre>
          </div>
        </div>
      </div>
    </li>
  );
};

// chat message list
const MessageList = function (props) {
  return (
    <ul>
      {
        props.messages.map((item, index) => (<MessageItem key={index} userName={item.userName} content={item.content} />))
      }
    </ul>);
}

// user item
const UserItem = function (props) {
  return (
    <li>
      <p>{props.userName}</p>
    </li>
  );
}

// user list
const UserList = function (props) {
  return (
    <ul>
    {
      props.users.map((item, index) => (
        <UserItem key={index} userName={item}/>
      ))
    }
    </ul>
  );
}

class ChatRoom extends React.Component {

  state = {
    userList: [],
    messages: []
  };
  componentDidMount () {
    webSocket.onmessage = (e) => {
      console.log(e.data);
      let data = JSON.parse(e.data);
      var messages = [];
      var userList = [];
      switch(data.action) {
        case 'getUserList':
          this.setState({
            userList: data.data.userList
          });
          break;
        case 'chatMessage':
          messages = [...this.state.messages];
          messages.push(data.data);
          this.setState({
            messages
          });
          break;
        case 'otherLogin':
          messages = [...this.state.messages];
          messages.push({
            userName: '系统消息',
            content: data.data.userName+' 进入了房间'
          });
          userList = [...this.state.userList];
          userList.unshift(data.data.userName);
          this.setState({
            messages,
            userList
          });
          break;
        case 'logout':
          userList = [...this.state.userList];
          userList = userList.filter((item) => {
            return item !== data.data.userName;
          });
          messages = [...this.state.messages];
          messages.push({
            userName: '系统消息',
            content: data.data.userName+' 离开了房间'
          });
          this.setState({
            userList,
            messages
          });
      }
    }
    webSocket.send(JSON.stringify({
      action: 'getUserList',
      data: {
        
      }
    }));
  }

  handleMessage = (message) => {
    webSocket.send(JSON.stringify({
      action: 'chatMessage',
      data: {
        content: message
      }
    }));
  };

  handleLeave = () => {
    webSocket.close();
    window.location.reload();
  };

  render () {
    return (
      <div className="room-info">
        <div className="user-list">
          <h5>当前在线：</h5>
          <ul>
            <UserList users={this.state.userList} />
          </ul>
        </div>
        <div className="message-box">
          <MessageList messages={this.state.messages}/>
          <ChatInput onMessage={this.handleMessage} onLeave={this.handleLeave}/>
        </div>
      </div>
    );
  }
}

export default ChatRoom;
