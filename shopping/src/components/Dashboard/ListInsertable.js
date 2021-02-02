import React, {Component} from "react";
import {
  Container,
  Row,
  Col,
  ListGroup,
  Button,
  Form,
  FormControl,
  InputGroup
} from 'react-bootstrap';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome'
import {faPlus, faTrashAlt, faLink, faUnlink} from '@fortawesome/free-solid-svg-icons'
import PropTypes from 'prop-types';
import * as Helper from '../../components/Helper';
import ItemLink from './ItemLink';

class ListInsertable extends Component {
  constructor(props) {
    super(props);
    this.state = {
      link: this.props.link
    };

    /* Initializarea metodelor publice */
    this.doAdd = this.doAdd.bind(this);
    this.doUnlink = this.doUnlink.bind(this);
  }

  /**
   * Actualizarea proprietatilor componentei
   *
   * @param object newProps: noile proprietati
   * @return void
   */
  componentWillReceiveProps(newProps) {
    this.setState({link: newProps.link})
  }

  /**
   * Adaugare e unui elemnt in lista
   *
   * @param object e: eveniment
   * @return void
   */
  doAdd(e) {
    e.preventDefault();
    this.props.onAdd(this.searchInput.value);
    this.searchInput.value = '';
  }

  /**
   * Stergere a asocierii unui element
   *
   * @param object e: eveniment
   * @return void
   */
  doUnlink(e) {
    e.preventDefault();
    let {link} = this.state;
    this.props.onUnlink(0);
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render(item) {
    let {link} = this.state;
    return (<ListGroup.Item key={0} className="shopping-list_insertable list_item mt-0 mb-4 p-0">
      <Container>
        <Row>
          <Col xs={12} className="p-0">
            <Form onSubmit={(e) => this.doAdd(e)} inline="inline" className="list_item_name">
              <InputGroup className="m-0 w-100">
                <FormControl ref={(input) => {
                    this.searchInput = input
                  }} placeholder="Introdu numele produsului" aria-label="Introdu numele produsului"/>
                <InputGroup.Append>
                  <Button type="submit" className="insertable_btn" variant="black">
                    <FontAwesomeIcon icon={faPlus}/>
                  </Button>

                </InputGroup.Append>
                <InputGroup.Append>
                  {
                    link != -1 && <Button variant="outline-dark" className="insertable_btn" onClick={(e) => this.doUnlink(e)}>
                        <FontAwesomeIcon icon={faUnlink}/>
                      </Button>
                  }
                </InputGroup.Append>

              </InputGroup>
            </Form>
          </Col>
        </Row>
        {link != -1 && <ItemLink item={link} hideDetails={true}/>}
      </Container>
    </ListGroup.Item>);
  }
}

export default ListInsertable;
