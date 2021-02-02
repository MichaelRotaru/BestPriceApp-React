import React, { Component } from 'react';
import { Redirect } from 'react-router-dom';
import { Spinner } from '@blueprintjs/core';
import { app } from '../../base';
import {Container, Row, Col} from 'react-bootstrap';

class Logout extends Component {
  constructor(props){
    super();
    this.state = {
       redirect: false
    }
  }

  /**
   * Metoda asociata evenimentului de incarcare a unei componente
   *
   * @return void
   */
  componentWillMount(){
    app.auth().signOut().then((user) => {
      this.setState({redirect: true})
    })
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    if(this.state.redirect === true){
      return <Redirect to="/" />
    }

    return (<Container fluid="fluid" className="section yellow d-flex justify-content-center align-items-center">
      <Spinner/>
    </Container>)
  }
}

export default Logout;
