import React, {Component} from 'react';
import PropTypes from 'prop-types';
import classNames from 'classnames';
import './Input.css';

class Input extends Component
{
	static propTypes = {
		type: PropTypes.oneOf(['text', 'textarea', 'password', 'number', 'email']),
		size: PropTypes.oneOf(['small', 'normal', 'large']),
		autoComplete: PropTypes.oneOf(['on', 'off']),
		disabled: PropTypes.bool,
		name: PropTypes.string
	};

	static defaultProps = {
		type: 'text',
		size: 'normal',
		value: ''
	};

	handleChange (e: SyntheticInputEvent): void {
		const onChange = this.props.onChange;

		if (onChange) {
			onChange(e);
		}
	}

	handleFocus (e: SyntheticInputEvent): void {
		const onFocus = this.props.onFocus;

		if (onFocus) {
			onFocus(e);
		}
	}

	handleBlur (e: SyntheticInputEvent): void {
		const onBlur = this.props.onBlur;

		if (onBlur) {
			onBlur(e);
		}
	}

	render () {
		const {type, size, prepend, append, autoComplete, disabled, placeholder, value, ...otherProps} = this.props;
		let className = classNames('input-group', `input-size-${size}`);

		if (type === 'textarea') {
			return (
				<div className="input-group">
					<textarea
						disabled={disabled}
						value={this.props.value ? this.props.value : ''}
						onChange={this.handleChange.bind(this)}
						onFocus={this.handleFocus.bind(this)}
						onBlur={this.handleBlur.bind(this)}
						{...otherProps}
					/>
				</div>
			);
		} else {
			return (
				<div className={className}>
					{
						prepend &&
						<div className="input-group-prepend">{prepend}</div>
					}
					<input type={type}
						disabled={disabled}
						autoComplete={autoComplete}
						value={this.props.value ? this.props.value : ''}
						placeholder={placeholder}
						onChange={this.handleChange.bind(this)}
						onFocus={this.handleFocus.bind(this)}
						onBlur={this.handleBlur.bind(this)}
						{...otherProps}
						/>
          			{ append && <div className="input-group-append">{append}</div> }
				</div>
			);
		}

	}
}

export default Input;
