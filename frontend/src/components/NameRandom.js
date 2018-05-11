import React from 'react';
import Button from './Button.js';
import './NameRandom.css';

class NameRandom extends React.Component {
  render () {
    return (
      <div className="nr-box">
        <div className="name-input">
          <input type="text" value={'test'} disabled/>
          <Button size="small" className="change">换一个</Button>
        </div>
        <Button type="info" className="b-enter">进入</Button>
      </div>
    );
  }
}

export default NameRandom;
