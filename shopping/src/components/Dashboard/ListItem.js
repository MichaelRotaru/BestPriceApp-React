import React, {Component} from "react";
import {Container, Row, Col, ListGroup, Button} from 'react-bootstrap';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome'
import {faPlus, faMinusCircle, faLink, faUnlink} from '@fortawesome/free-solid-svg-icons'
import * as Helper from '../../components/Helper';
import ItemLink from './ItemLink';
import EditableTextClick from './../EditableTextClick';

class ListItem extends Component {
  constructor(props) {
    super(props);
    this.state = {
      value: this.props.item,
      linked: this.props.hideDetails || false,
      selected: false
    };

    /* Initializarea metodelor publice */
    this.doDelete = this.doDelete.bind(this);
    this.doUnlink = this.doUnlink.bind(this);
    this.doLink = this.doLink.bind(this);
    this.doInputValueChanged = this.doInputValueChanged.bind(this);
  }

  /**
   * Actualizarea proprietatilor componentei
   *
   * @param object newProps: noile proprietati
   * @return void
   */
  componentWillReceiveProps(newProps) {
    this.setState({value: newProps.item})
  }

  /**
   * Stergerea e unui elemnt in lista
   *
   * @param object e: eveniment
   * @return void
   */
  doDelete(e) {
    e.preventDefault();
    let {value} = this.state;
    this.props.onDelete(value.key);
  }

  /**
   * Legarea e unui elemnt din lista cu unul din lista de sugestii
   *
   * @param object e: eveniment
   * @return void
   */
  doLink(e) {
    e.preventDefault();
    let {value} = this.state;
    this.props.onLink(value.key);
  }

  /**
   * Stergere a asocierii unui element
   *
   * @param object e: eveniment
   * @return void
   */
  doUnlink(e) {
    e.preventDefault();
    let {value} = this.state;
    this.props.onUnlink(value.key);
  }

  /**
   * Actualizarea valorii unui input
   *
   * @param string newVal: noua valoare
   * @return void
   */
  doInputValueChanged(newVal) {
    let {value} = this.state;
    this.props.onChanged(newVal,value.key);
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    let {value,selected} = this.state;
    let linkButton;
    if (value.link != -1) {
      linkButton = <div className="shopping-list_link-btn" onClick={(e) => this.doUnlink(e)}><FontAwesomeIcon icon={faUnlink}/></div>;
    } else {
      linkButton = <div className="shopping-list_link-btn" onClick={(e) => this.doLink(e)}><FontAwesomeIcon icon={faLink}/></div>;
    }

    return (<ListGroup.Item key={value.key} action="action" className={selected?'selected list_item':'list_item'}>
      <Container fluid>
        <Row>
          <Col xs={10} className="p-0">
            <EditableTextClick value={value.name} editClassName="form-control list_item_name" spanClassName="list_item_name" onValueChanged={(newName) => this.doInputValueChanged(newName)}/>
          </Col>
          <Col xs={2} className="p-0 text-right">
            {linkButton}
            <div className="shopping-list_trash-btn" onClick={(e) => this.doDelete(e)}><FontAwesomeIcon icon={faMinusCircle}/></div>
          </Col>
        </Row>
        {value.link != -1 && <ItemLink item={value.link}/>}
      </Container>
    </ListGroup.Item>);
  }
}

export default ListItem;
