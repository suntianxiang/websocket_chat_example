import React, { Component } from 'react';
import PropTypes from 'prop-types';
import './Button.css';
import classNames from 'classnames';

class Button extends Component {
	onClick (e: SyntheticEvent): void {
		if (this.props.onClick) {
	    	this.props.onClick(e);
	    }
	}

	render () {

		return (<button
				className={classNames('btn', 'btn-' + this.props.type, 'btn-' + this.props.size, this.props.className)}
				type={this.props.nativeType}
				disabled={this.props.disabled}
				onClick={this.onClick.bind(this)}
			>
				<span>{this.props.children}</span>
			</button>);
	}
}

Button.propTypes = {
	nativeType: PropTypes.oneOf(['button', 'submit']),
	disabled: PropTypes.bool,
	type: PropTypes.oneOf(['default', 'primary', 'info', 'warning', 'danger']),
	size: PropTypes.oneOf(['normal', 'small', 'large'])
};

Button.defaultProps = {
	nativeType: 'button',
	disabled: false,
	type: 'default',
	className: '',
	size: 'normal'
};

export default Button;
