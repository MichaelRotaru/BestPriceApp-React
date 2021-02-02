import React, {Component} from "react";
import {ListGroup, Container, Row, Col, InputGroup ,Form, FormControl, Button} from 'react-bootstrap';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome'
import {faPlus, faTrashAlt, faLink, faUnlink} from '@fortawesome/free-solid-svg-icons'
import EditableLabel from 'react-inline-editing';
import * as Helper from '../../components/Helper';
import ItemLink from '../../components/Dashboard/ItemLink';
import ListItem from '../../components/Dashboard/ListItem';
import ListInsertable from '../../components/Dashboard/ListInsertable';


class ShoppingList extends Component {
  constructor(props) {
    super(props);
    this.state = {
      title: this.props.title,
      groups: this.props.groups,
      selected: this.props.selected,
      selectedItem: -1,
    };
    /* Initializarea metodelor publice ale componentei */
    this.doAdd = this.doAdd.bind(this);
    this.doDelete = this.doDelete.bind(this);
    this.doLink = this.doLink.bind(this);
    this.doUnlink = this.doUnlink.bind(this);
    this.doSelect = this.doSelect.bind(this);
    this.doItemChanged = this.doItemChanged.bind(this);
    this.createListItem = this.createListItem.bind(this);
  }

  /**
   * Actualizarea proprietatilor componentei
   *
   * @param object newProps: noile proprietati
   * @return void
   */
  componentWillReceiveProps(newProps) {
    this.setState({title:newProps.title,groups:newProps.groups,selected:newProps.selected})
  }

  /**
   * Adaugarea unui produs in lista
   *
   * @param string name: Nume element
   * @param bool _ignoreLink: Ignora legatura cu o sugesti
   * @param object _groups: Intreaga colectie a carei apartine
   * @return void
   */
  doAdd(name, _ignoreLink = false, _groups = 0) {
    if(name.length){
      let item = {
        key: -1,
        name: name,
        link: this.state.selected,
      };

      /* Ignora legatura */
      if(_ignoreLink){
        item.link = -1;
      }

      let groupId = item.link!=-1 ? item.link.seller_id : "0";
      let sellerName = item.link!=-1 ? item.link.seller_name : "0";
      let itemPrice = item.link!=-1 ? item.link.price : 0;

      let groups = _groups?_groups:this.state.groups;
      let group = 0;
      let index = -1;

      /* Asociasa produsul cu grupul de vanzatori din care face parte */
      if(groups && groups.length){
        index = groups.findIndex(element => element.seller_id == groupId);
        group = groups[index];
      }

      item.key = groupId+'_'+Date.now();
      /* Determina pretul total al grupului */
      if(group){
        group.group_total += itemPrice;
        group.items = [item].concat(group.items);
        groups[index] = group;
      }else{
        group = {seller_id:groupId,seller_name:sellerName,group_total:itemPrice,items:[item]};
        groups = [group].concat(groups);
      }

      if(_groups){
        return groups;
      }else{
        this.props.onSelectSuggestion(-1);
        this.props.onUpdateShoppingGroups(groups);
      }
    }
  }

  /**
   * Adauga un produs in lista
   *
   * @param int key: Cheia elementului
   * @param object _groups: Intreaga colectie a carei apartine
   * @return void
   */
  doDelete(key, _groups = 0) {
    let groupId = key.split("_")[0];
    let itemId = key.split("_")[1];
    let groups = _groups?_groups:this.state.groups;
    let index = groups.findIndex(element => element.seller_id == groupId);
    let groupItems = groups[index].items;

    let itemPrice = 0;

    /* Elimina produsul din grupul corespunzator */
    groupItems = groupItems.filter(function(item) {
      if(item.key === key){
        itemPrice = item.link.price;
        return false;
      }else{
        return true;
      }
    });

    // Remove group and substract total
    if(!groupItems.length){
      groups = groups.filter(function(item) {
        return (item.seller_id !== groupId);
      });
    }else{
      groups[index].items = groupItems;
      if(itemPrice){
        groups[index].group_total -= itemPrice;
      }
    }

    if(_groups){
      return groups;
    }else{
      this.props.onUpdateShoppingGroups(groups);
    }
  }

  /**
   * Creeaza o legatura cu o sugestie
   *
   * @param int key: Cheia elementului
   * @return void
   */
  doLink(key){
    if(key && this.state.selected!=-1){
      let groupId = key.split("_")[0];
      let itemId = key.split("_")[1];
      let groups = JSON.parse(JSON.stringify(this.state.groups)); //deep clone
      let index = groups.findIndex(element => element.seller_id == groupId);
      let itemIndex = groups[index].items.findIndex(element => element.key == key);
      let itemName = groups[index].items[itemIndex].name;

      let removed = this.doDelete(key,groups);
      let readed = this.doAdd(itemName, false,removed);

      this.props.onUpdateShoppingGroups(readed);
    }else{
      this.setState({selected:-1});
    }
  }

  doUnlink(key){
    if(key){
      let groupId = key.split("_")[0];
      let itemId = key.split("_")[1];
      let groups = JSON.parse(JSON.stringify(this.state.groups)); //deep clone
      let index = groups.findIndex(element => element.seller_id == groupId);
      let itemIndex = groups[index].items.findIndex(element => element.key == key);
      let itemName = groups[index].items[itemIndex].name;

      let removed = this.doDelete(key,groups);
      let readed = this.doAdd(itemName, true, removed);

      this.props.onUpdateShoppingGroups(readed);
    }else{
      this.setState({selected:-1});
    }
  }

  /**
   * Modifica valoarea numelui unui element
   *
   * @param string newVal: Noua valoare
   * @param int key: Cheia elementului
   * @return void
   */
  doItemChanged(newVal, key){
    if(key){
      let groupId = key.split("_")[0];
      let itemId = key.split("_")[1];
      let groups = this.state.groups;
      let index = groups.findIndex(element => element.seller_id == groupId);
      let itemIndex = groups[index].items.findIndex(element => element.key == key);
      groups[index].items[itemIndex].name = newVal;

      this.props.onUpdateShoppingGroups(groups);
    }
  }

  /**
   * Selecteaza un elemnt din lista
   *
   * @param int key: Cheia elementului
   * @return void
   */
  doSelect(key){
    this.setState({selectedItem:key});
  }

  /**
   * Randeaza un element din lista
   *
   * @param object item: Informatiile elemntului
   * @return valoarea in format HTML a elementului
   */
  createListItem(item) {
    return (
      <ListItem key={item.key} item={item} onSelect={this.doSelect}  onLink={this.doLink} onUnlink={this.doUnlink} onDelete={this.doDelete} onChanged={this.doItemChanged}/>
    );
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    let {groups} = this.state;
    let groupsItems = [];
    if(groups.length){
       groupsItems = groups.map((value, index) => {
        let listItems = value.items.map(this.createListItem);
        let groupTotal = value.group_total;

        let groupName;
        if(value.seller_name != 0){
          groupName = <div className="shopping-list_seller-group_name"><span>{value.seller_name}</span></div>;
          groupTotal = <div className="shopping-list_seller-group_total"><span>{Helper.formatCurrency(groupTotal)}</span></div>;
        }else{
          groupName = <div className="shopping-list_seller-group_name"><span className="empty"></span></div>;
          groupTotal = <div className="shopping-list_seller-group_total m-0"><span className="empty m-0"></span></div>;
        }

        return <div className="shopping-list_seller-group">
          {groupName}
          {listItems}
          {groupTotal}
        </div>
      })
    }

    return (<ListGroup className="list" variant="flush">
      <ListInsertable link={this.state.selected} onUnlink={this.doUnlink} onAdd={this.doAdd}/>
      <Container fluid className="shopping-list_groups p-0">
        {groupsItems}
      </Container>
    </ListGroup>);
  };
}

export default ShoppingList;
