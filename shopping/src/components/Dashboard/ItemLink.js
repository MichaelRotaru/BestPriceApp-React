import React, {Component} from "react";
import {Container, Row, Col, Image} from 'react-bootstrap';
import * as Helper from '../../components/Helper';

class ItemLink extends Component {
  constructor(props) {
    super(props);
    this.state = {
      value: this.props.item,
      hideDetails: this.props.hideDetails
    };
  }

  /**
   * Actualizarea proprietatilor componentei
   *
   * @param object newProps: noile proprietati
   * @return void
   */
  componentWillReceiveProps(newProps) {
    this.setState({value: newProps.item, hideDetails: newProps.hideDetails})
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    let {value, hideDetails} = this.state;
    let linkHeader = <Row>
      <Col xs={8} className="p-0">
        <a href={value.url} className="list_item_title " target="_blank">{value.title}</a>
      </Col>
      <Col xs={4} className="p-0">
        <p className="list_item_price text-right">{Helper.formatCurrency(value.price)}</p>
      </Col>
    </Row>;

    let linkDetails = <Row className="list_item_info-wp">
      <Col xs={2} className="p-0">
        <Image className="list_item_thumb" src={value.thumb} thumb="thumb"/>
        <Image className="list_item_thumb" src={value.seller_logo} thumb="thumb"/>
      </Col>
      <Col xs={10}>
        <div>
          <p className="list_item_sdesc" dangerouslySetInnerHTML={{
              __html: value.short_desc
            }}/>
        </div>
      </Col>
    </Row>;

    if (!hideDetails) {
      return <Container fluid="fluid" className="link p-0 m-0">
        {linkHeader}
        {linkDetails}
      </Container>;
    } else {
      return <Container fluid="fluid" className="link pt-2 pb-0 pl-2 pr-2">
        {linkHeader}
      </Container>;
    }
  }
}

export default ItemLink;
