import React, { Component } from 'react';
import './App.css';
import Home from './views/Home.js';
import ChatRoom from './views/ChatRoom.js';
import Button from './components/Button.js';
import webSocket from './api/webSocket.js';

class App extends Component {
  state = {
    connecting: true,
    userName: '',
    enter: false,
  };

  componentDidMount () {
    webSocket.onopen = (e) => {
      this.setState({
        connecting: true
      });
      webSocket.send(JSON.stringify({
        action: 'login',
        data: {
        }
      }));
    };

    webSocket.onmessage = (e) => {
      console.log(e.data);
      let data = JSON.parse(e.data);
      if (data.action == 'login_back' && data.code == 0) {
        this.setState({
          userName: data.data.userName
        });
      }
    };

    webSocket.onclose = (e) => {
      this.setState({
        connecting: false
      });
    };

    webSocket.onerror = (e) => {
      console.log(e);
    };
  }

  handleEnter = () => {
    this.setState({
      enter: true
    });
  }

  render() {
    return (
      <div className="App">
        {
          this.state.enter ? <ChatRoom userName={this.state.userName}/>
          :
          <div className="before-enter">
            <Home userName={this.state.userName} onEnter={this.handleEnter}/>
            {
              !this.state.connecting ?
              <Button type="danger" className="mt-10" onClick={() => {
                window.location.reload();
              }}>连接丢失，点击重连</Button>
              : ''
            }
          </div>

        }
      </div>
    );
  }
}

export default App;
