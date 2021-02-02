import React, {Component} from "react";
import {ListGroup, Container, Row, Col, Image} from 'react-bootstrap';
import * as Helper from '../../components/Helper';
import noresults from '../../images/noresults.png';

const localPath = "http://localhost:8888/disertatie/admin/uploads/";

class SuggestionList extends Component {
  constructor(props) {
    super(props);
    this.state = {
      items: [],
      selected: -1
    };
    this.scrollAnchorElem = React.createRef();
    /* Initializarea metodelor publice ale componentei */
    this.doSelectItem = this.doSelectItem.bind(this);
    this.doCreateTasks = this.doCreateTasks.bind(this);
  }

  /**
   * Metoda asociata evenimentului finalizare a procesului de incarcare a unei componente
   *
   * @return void
   */
  componentDidMount(){
    this.doScrollTo(this.scrollAnchorElem.current.offsetTop);
    /**
     * Forteaza cautarea
    this.doSearchItem("asus");
     */
  }

  /**
   * Selecteaza un element din lista
   *
   * @param int key: Cheie elementului parinte
   * @param bool sel_key: Cheia vanzatorului 
   * @return void
   */
  doSelectItem(key,sel_key) {
    return event => {
      if (key != -1) {
        var mainItem = this.state.items[key];
        var seller = mainItem.sellers.filter(function(sel) {
          if(sel.seller_key === sel_key){
            return sel;
          }else{
            return false;
          }
        });
        var newItem = {...mainItem,...seller[0]};
        this.setState({selected: newItem});
        this.props.onSelect(newItem);
        this.doScrollTo(this.scrollAnchorElem.current.offsetTop);
      }
    }
  }

  /**
   * Scroleaza pagina la pozitia specificata
   *
   * @param int pos - Pozitia la care se doreste scrolarea paginii
   * @return void
   */
  doScrollTo(pos = 0){
    if(pos){
      window.scrollTo(0, pos);
    }else{
      window.scrollTo(0, pos);
    }
  }

  /**
   * Initializeaza cautarea unui element in baza de date
   *
   * @param string searchValue - Valoarea elementului
   * @return void
   */
  doSearchItem(searchValue) {
    this.doScrollTo(0);
    /* Verifica validitatea termenilor */
    if(!searchValue){
      this.setState({items: []});
    }else{
      /* Adresa URL a API-ului de cautare. Parametrul de cautare s este adaugat in URL prin metoda GET */
      let url = 'http://localhost:8888/disertatie/admin/filter.php?s=' + searchValue;
      fetch(url).then((response) => {
        return response.json();
      }).then((results) => {
        /* Parseaza raspunsul la formatul aplicatiei */
        if (results !== undefined) {
          let _items = results.map((res, i) => {
            var sellers = res.sellers;
            let _sellers = sellers.map((sel, j) => {
              return {
                seller_key: j,
                seller_id: sel.id,
                seller_name: sel.name,
                seller_logo: localPath+sel.id+"/"+sel.logo,
                price: Number(sel.price),
                url: sel.url,
                seller_home_url: sel.home_url,
              }
            })

            return {
              key: i,
              title: res.title,
              thumb: localPath+res.thumb,
              short_desc: res.short_desc,
              sellers: _sellers.sort((a, b) => a.price - b.price)
            }
          })
          console.log(_items);
          this.setState({items: _items});
        }
      }).catch((error) => {
        alert(error);
        this.setState({items: []});
      });
    }
  }

  /**
   * Randeaza un element din lista
   *
   * @param object item: Informatiile elemntului
   * @return valoarea in format HTML a elementului
   */
  doCreateTasks(item) {
    console.log(item);
    let sellers = item.sellers.map((sel, i) => {
      return (
        <ListGroup.Item ref={this.itemsListElem} action="action" onClick={this.doSelectItem(item.key,sel.seller_key)} key={item.key} className="list_item">
          <Container>
            <Row>
              <Col xs={3}>
                <img class="list_item_logo w-100" src={sel.seller_logo}/>
              </Col>
              <Col xs={9} class="align-right">
                <p class="list_item_price">{Helper.formatCurrency(sel.price)}</p>
                <a href={sel.url} target="_blank" class="list_item_site">vezi pe {sel.seller_home_url}</a>
              </Col>
            </Row>
          </Container>
        </ListGroup.Item>
      )
    });
    return (<div><ListGroup.Item className="list_item head">
        <Container>
          <Row>
            <Col xs={3} className="p-0">
              <img class="list_item_thumb" src={item.thumb}/>
            </Col>
            <Col xs={9}>
              <div class="list_item_info-wp">
                <p class="list_item_title">{item.title}</p>
                <p class="list_item_sdesc" dangerouslySetInnerHTML={{ __html: item.short_desc }}/>
              </div>
            </Col>
          </Row>
        </Container>
    </ListGroup.Item>{sellers} </div>) ;
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    let {items} = this.state;
    let listItems = items.map(this.doCreateTasks);
    if(!items.length){
      return (<Col xs={12} md={6} xl={4} className="d-flex align-items-center section section-suggestions order-2 order-md-3 pt-md-0">
        <Image src={noresults} fluid />
        <div ref={this.scrollAnchorElem}></div>
      </Col>);
    }else{
      return (<Col xs={12} md={6} xl={4} className="section section-suggestions order-2 order-md-3 pt-md-0">
        <div>
          <Row>
            <ListGroup className="suggestion-list" variant="flush">
              {listItems}
            </ListGroup>
          </Row>
        </div>
        <div ref={this.scrollAnchorElem}></div>
      </Col>);
    }
  };
}

export default SuggestionList;
