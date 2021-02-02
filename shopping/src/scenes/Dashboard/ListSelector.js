import React, {Component} from "react";
import {
  ListGroup,
  Container,
  Row,
  Col,
  InputGroup,
  Form,
  FormControl,
  Button
} from 'react-bootstrap';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome'
import {faPlus, faTimesCircle} from '@fortawesome/free-solid-svg-icons'
import EditableTextButton from '../../components/EditableTextButton';
import Nav from 'react-bootstrap/Nav';
import {Link} from 'react-router-dom';

class ListSelector extends Component {
  constructor(props) {
    super(props);
    this.state = {
      selected: this.props.selected || 0,
      lists: this.props.lists || [],
      cssHidden: false
    };
    /* Initializarea metodelor publice ale componentei */
    this.doAdd = this.doAdd.bind(this);
    this.doDelete = this.doDelete.bind(this);
    this.doSelect = this.doSelect.bind(this);
  }

  /**
   * Actualizarea proprietatilor componentei
   *
   * @param object newProps: noile proprietati
   * @return void
   */
  componentWillReceiveProps(newProps) {
    this.setState({lists: newProps.lists, cssHidden: !newProps.mobileMenuOpened});
  }

  /**
   * Adauga o lista in colectia de liste
   *
   * @param event e: Eveniment
   * @param string val: Numele listei
   * @return void
   */
  doAdd(e,val=0) {
    if(e){
      e.preventDefault();
    }
    /* Daca numele nu este null */
    if(!val){
      val = this.list_name_input.value;
    }
    /* Daca lungimea numelui este valida */
    if (!val.length) {
      return false;
    }
    let {lists} = this.state;
    if(lists.length >=20){
      return false;
    }
    let newList = {
      title: val,
      groups: []
    }
    /* Adauga lista la colectia de liste */
    lists = (
      lists.length
      ? lists
      : []).concat([newList]);
    /* Reseteaza campul de introducere a numelui */
    this.list_name_input.value = '';
    this.props.onItemsChanged(lists);
    /* Selecteaza lista adaugata */
    this.doSelect(lists.length - 1);
  }

  /**
   * Sterge o lista din colectia de liste
   *
   * @param int key: Cheia listei ce se doreste a fi stearsa
   * @param event e: Eveniment
   * @return void
   */
  doDelete(key, e) {
    let {lists} = this.state;
    if (key !== -1 && lists.length > 1) {
      lists.splice(key, 1);
      this.props.onItemsChanged(lists);
      if (this.state.selected >= key) {
        /* Selecteaza elementul precedent sau ultimul element din lista */
        this.doSelect(
          lists.length
          ? Math.max(0, this.state.selected - 1)
          : -1,
        null);
      }
    }
  }

  /**
   * Modifica valoarea numelui unei liste
   *
   * @param int key: Cheia elementului
   * @param string newName: Noul nume
   * @return void
   */
  doInputValueChanged(key, newName) {
    let {lists} = this.state;
    lists[key].title = newName;
    this.props.onItemsChanged(lists);
    this.setState({lists});
  }

  /**
   * Selecteaza o lista din colectie
   *
   * @param int key: Cheia elementului
   * @param event e: Eveniment
   * @return void
   */
  doSelect(key, e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation(true);
    }
    this.props.onSelectChanged(parseInt(key));
    this.setState({selected: parseInt(key)});
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    let {lists, cssHidden} = this.state;
    let selectedClass = '';
    return (<Col xs={12} md={12} xl={4} className={cssHidden
        ? "closed section yellow section-list-selector"
        : "section yellow section-list-selector"}>
      <Container fluid="fluid" className="list-selector">
        <Row className=" d-flex align-items-center h-100">
          <Container>
            <ListGroup className="ListSelector-list" variant="flush">
              {
                Object.keys(lists).map((id) => {
                  if (this.state.selected == id) {
                    selectedClass = "selected";
                  } else {
                    selectedClass = '';
                  }
                  return (<ListGroup.Item className={selectedClass} key={id}>
                    <Container>
                      <Row>
                        <Col xs={12}>
                          <div onClick={(e) => this.doSelect(id, e)} className="d-inline-block">
                            <EditableTextButton value={lists[id].title} editClassName="form-control" onValueChanged={(newName) => this.doInputValueChanged(id, newName)}/>
                          </div>
                          <span className="ListSelector-list_remove icon" onClick={(e) => this.doDelete(id, e)}><FontAwesomeIcon icon={faTimesCircle}/></span>
                        </Col>
                      </Row>
                    </Container>
                  </ListGroup.Item>)
                })
              }

              <ListGroup.Item>
                <Container>
                  <Row>
                    <Col xs={12}>
                      <Form onSubmit={(e) => this.doAdd(e)} inline="inline">
                        <InputGroup>
                          <FormControl ref={(input) => {
                              this.list_name_input = input
                            }} placeholder="Adauga o lista noua" aria-label="Adauga o lista noua"/>
                          <InputGroup.Append>
                            <Button type="submit" variant="black">
                              <FontAwesomeIcon icon={faPlus}/>
                            </Button>
                          </InputGroup.Append>
                        </InputGroup>
                      </Form>
                    </Col>
                  </Row>
                </Container>
              </ListGroup.Item>
            </ListGroup>
          </Container>
        </Row>
      </Container>
    </Col>)
  };
}

export default ListSelector;
