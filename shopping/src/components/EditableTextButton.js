import React, {Component} from "react";
import {ListGroup, Container, Row, Col, InputGroup ,Form, FormControl, Button} from 'react-bootstrap';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome'
import {faPen} from '@fortawesome/free-solid-svg-icons'

var CHAR_LIMIT = 20;

class EditableTextButton extends Component{
  constructor(props){
    super(props)
    this.state = {
      name: props.name,
      type: props.type||'text',
      value: props.value||'',
      spanClassName: props.spanClassName,
      editClassName: props.editClassName,
      edit: false
    }
  }

  /**
   * Actualizarea proprietatilor componentei
   *
   * @param object newProps: noile proprietati
   * @return void
   */
  componentWillReceiveProps(newProps) {
    this.setState({name:newProps.name,value:newProps.value});
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    return (
      this.state.edit===true &&
      <input
        name={this.state.name}
        type={this.state.type}
        value={this.state.value}
        className={this.state.editClassName}
        autoFocus
        onFocus={event=>{
          const value = event.target.value
          event.target.value = ''
          event.target.value = value
          this.setState({backup:this.state.value.substring(0, CHAR_LIMIT)})
        }}
        onChange={event=>{
          this.setState({value:event.target.value.substring(0, 20)})
        }}
        onBlur={event=>{
          this.setState({edit:false});
          this.props.onValueChanged(this.state.value.length?this.state.value.substring(0, 20):this.state.backup);
        }}
        onKeyUp={event=>{
          if(event.key==='Escape') {
            this.setState({edit:false, value:this.state.backup})
            this.props.onValueChanged(this.state.backup);
          }
          if(event.key==='Enter') {
            this.setState({edit:false})
            this.props.onValueChanged(this.state.value.length?this.state.value.substring(0, 20):this.state.backup);
          }
        }}
      />
      ||
      <div className={this.state.spanClassName}>
        <span>
          {this.state.value}
        </span>
        <span onClick={event=>{
            this.setState({edit:this.state.edit!==true})
          }} className="icon"> <FontAwesomeIcon icon={faPen}/>
        </span>
      </div>
    )
  }
}

export default EditableTextButton;
