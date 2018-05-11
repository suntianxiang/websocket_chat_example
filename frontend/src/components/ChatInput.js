import React from 'react';
import Input from './Input.js';
import Button from './Button.js';

class ChatInput extends React.Component {
	state = {
		value: ''
	};

	handleChange = (e) => {
		this.setState({
			value: e.target.value
		});
	}

	handleMessage = () => {
		this.props.onMessage(this.state.value);
		this.setState({value: ''});
	}

	render () {
		return (
			<div className="chat-input">
				<Input
					name="editMessage"
					onChange={this.handleChange}
					type="textarea"
					rows="6"
					placeholder="输入消息"
					value={this.state.value}/>
				<div className="btn-bar">
					<Button type="primary" onClick={this.handleMessage}>发送</Button>
					<Button type="danger" onClick={this.props.onLeave}>离开</Button>
				</div>
			</div>
		);
	}
}

export default ChatInput;
