import React, {Component} from "react";
import {Redirect, Link} from 'react-router-dom'
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

import {Toaster, Intent} from '@blueprintjs/core'
import {app, base} from '../../base'

import {FontAwesomeIcon} from '@fortawesome/react-fontawesome'
import {faSearch, faTimesCircle} from '@fortawesome/free-solid-svg-icons'

import ShoppingList from "./ShoppingList";
import SuggestionList from "./SuggestionList";
import Navigator from '../../components/Navigator';
import ListSelector from './ListSelector';
import * as Helper from '../../components/Helper';

class Dashboard extends Component {
  constructor(props) {
    super(props);
    this.state = {
      currentUser: this.props.currentUser,
      currentUserProfile: this.props.currentUserProfile,
      searchedSuggestion: '',
      selectedSuggestion: -1,
      selectedList: 0,
      mobileMenuOpened: false,
      lists: []
    };
    /* Initializearea metodelor publice ale componentei */
    this.doSync = this.doSync.bind(this);
  }

  /**
   * Metoda asociata evenimentului de incarcare a unei componente
   *
   * @return void
   */
  componentWillMount() {
    let that = this;
    /* Verifica daca exista liste in baza de date */
    base.fetch(`lists/${that.state.currentUser.uid}`, {
      context: that,
      then(data) {
        if (!data.length) {
          /* Creeaza o lista in cazul in care nu exista nici una */
          base.update(`lists/${that.state.currentUser.uid}`, {
            data: {
              0: { title: 'Lista mea' }
            },
            then(err) {}
          });
        }
      }
    })
    this.doSync();
  }

  /**
   * Initializeaza sincronizarea obiectului 'state' al componentei cu baza de date
   *
   * @return void
   */
  doSync() {
    base.syncState(`lists/${this.state.currentUser.uid}`, {
      context: this,
      state: 'lists'
    })
  }

  /**
   * Metoda asociata evenimentului de demontarea unei componente
   *
   * @return void
   */
  componentWillUnmount() {
    base.removeBinding(this.state.lists);
  }

  /**
   * Aplica o cautare de produs
   *
   * @param event e: Eveniment
   * @return void
   */
  doSearchSuggestion(e) {
    e.preventDefault();
    if (this.searchInput.value !== "" && this.searchInput.value != this.state.searchedSuggestion) {
      this.setState({selectedSuggestion: -1, searchedSuggestion: this.searchInput.value});
      this.suggestionListElem.doSearchItem(this.searchInput.value);
    }
  }

  /**
   * Modifica listele de produse
   *
   * @param object lists: Obiect ce contine toate listele
   * @return void
   */
  doUpdateLists(lists) {
    this.setState({lists});
  }

  /**
   * Selecteaza o lista
   *
   * @param object selectedList: Cheia listei selectate
   * @return void
   */
  doUpdateSelectedList(selectedList) {
    this.setState({selectedList});
  }

  /**
   * Selecteaza o sugestie de cautare
   *
   * @param object selectedSuggestion: Obiectul ce contine informatiile sugestiei de cautare
   * @return void
   */
  doSelectSuggestion(selectedSuggestion) {
    this.setState({selectedSuggestion});
  }

  /**
   * Modifica elementele din lista de produse
   *
   * @param array groups: Grupurile de liste de produse
   * @return void
   */
  doUpdateShoppingGroups(groups) {
    let {lists} = this.state;
    lists[this.state.selectedList].groups = groups;
    this.setState({lists: lists});
  }

  /**
   * Sterge valoarea din campul de cautarea si reseteaza sugestiile de cautare
   *
   * @param event e: Eveniment
   * @return void
   */
  doClearSeachInput(e) {
    e.preventDefault();
    this.searchInput.value = '';
    this.setState({selectedSuggestion: -1, searchedSuggestion: ''});
    this.suggestionListElem.doSearchItem(0);
  }

  /**
   * Ascunderea/Afisarea meniului in versiunea mobile
   *
   * @param bool newVal: Valoarea de vizibilitate a sectiunii
   * @return void
   */
  doToggleMobileMenu(newVal) {
    this.setState({mobileMenuOpened: newVal});
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    let {lists, currentUser, currentUserProfile, searchedSuggestion} = this.state;
    let currentList = lists[this.state.selectedList];
    let listTitle = '';

    if (currentList) {
      listTitle = currentList.title;
    }

    return (<Container fluid="fluid" className="p-0">
      <Toaster ref={(element) => {
          this.toaster = element
        }}/>
      <Navigator toggleMobileMenu={this.doToggleMobileMenu.bind(this)} userName={currentUserProfile && currentUserProfile.name
          ? currentUserProfile.name
          : currentUser.email}/>
      <Row>

        <ListSelector mobileMenuOpened={this.state.mobileMenuOpened} lists={this.state.lists} onItemsChanged={this.doUpdateLists.bind(this)} onSelectChanged={this.doUpdateSelectedList.bind(this)}/>

        <Col xs={12} md={6} xl={4} className="section section-shopping-list yellow pt-xl-0 order-3 order-md-2">
          <Container className="shopping-list-wp">

            <Row className="searchbar pl-0 pr-0">
              <Col xs={12}>
                <Form onSubmit={this.doSearchSuggestion.bind(this)} inline="inline">
                  <InputGroup className="m-0 w-100">
                    <FormControl type="text" ref={(a) => this.searchInput = a} placeholder="Cauta produsul online"/>
                    <InputGroup.Append>
                      <Button variant="black" type="submit"><FontAwesomeIcon icon={faSearch}/></Button>
                      {searchedSuggestion && <Button variant="outline-dark" type="submit" onClick={this.doClearSeachInput.bind(this)}><FontAwesomeIcon icon={faTimesCircle}/></Button>}
                    </InputGroup.Append>
                  </InputGroup>
                </Form>
              </Col>
            </Row>

            <Row className="shopping-list">
              {
                currentList && <ShoppingList selected={this.state.selectedSuggestion} title={listTitle} groups={currentList.groups
                      ? currentList.groups
                      : []} onUpdateShoppingGroups={this.doUpdateShoppingGroups.bind(this)} onSelectSuggestion={this.doSelectSuggestion.bind(this)}/>
              }
            </Row>
          </Container>
        </Col>
        <SuggestionList ref={(a) => this.suggestionListElem = a} onSelect={this.doSelectSuggestion.bind(this)}/>
      </Row>
    </Container>);
  }
}

export default Dashboard;
