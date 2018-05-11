import React from 'react';
import Button from '../components/Button.js';
import PropTypes from 'prop-types';

class Home extends React.Component {
  render () {
    return (
      <div className="home">
        <h3>Anonymous chat room demo</h3>
        <p>your random name:<strong>{this.props.userName}</strong></p>
        <Button type="info" className="b-enter" onClick={this.props.onEnter}>进入</Button>
      </div>
    );
  }
}

Home.propTypes = {
  userName: PropTypes.string.isRequired,
  onEnter: PropTypes.func.isRequired
};

export default Home;
