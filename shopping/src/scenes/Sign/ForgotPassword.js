import React, {Component} from 'react'
import {Redirect} from 'react-router-dom'
import {Toaster, Intent} from '@blueprintjs/core'
import {
  Container,
  Row,
  Col,
  Form,
  FormControl,
  Button
} from 'react-bootstrap';
import {Link} from 'react-router-dom';

import {app, base} from '../../base';

class ForgotPassword extends Component {
  constructor(props) {
    super(props);
    this.state = {
      redirect: this.props.authenticated
    }
    /* Initializarea metodelor publice ale componentei */
    this.doAction = this.doAction.bind(this);
  }

  /**
   * Actualizarea proprietatilor componentei
   *
   * @param object newProps: noile proprietati
   * @return void
   */
  componentWillReceiveProps(nextProps) {
    this.setState({redirect: nextProps.authenticated})
  }

  /**
   * Aplica formularul
   *
   * @param event e: Eveniment
   * @return void
   */
  doAction(e) {
    e.preventDefault();
    let email = this.emailInput.value

    if (!email) {
      this.toaster.show({intent: Intent.DANGER, message: "Adresa de email invalida"});
      return false;
    }
    let that = this;
    app.auth().sendPasswordResetEmail(email)
      .then(function(user) {
          that.toaster.show({intent: Intent.SUCCESS, message: "Un email cu link-ul de resetarea a fost trimis!"});
          setTimeout(() => {
            that.setState({redirect:true})
          }, 2000);
      }).catch(function(err){
          that.toaster.show({intent: Intent.DANGER, message: err.message});
      });
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    const {from} = this.props.location.state || {
      from: {
        pathname: '/'
      }
    }
    if (this.state.redirect === true) {
      return <Redirect to={from}/>
    }

    return (<Container fluid="fluid" className="p-0">
      <Toaster ref={(element) => {
          this.toaster = element
        }}/>
      <Row>
        <Col xs={12} md={6} xl={4} className="section yellow d-flex align-items-center sidebar">
          <Container fluid className="sidebar_wp">
            <h2 className="sidebar_title">Ai uitat parola?</h2>
            <p className="sidebar_desc">Introdu adresa de email in formularul alaturat.</p>
            <Link className="btn btn-outline-dark" to="/">Autentificare</Link>
          </Container>
        </Col>
        <Col xs={12} md={6} xl={4} className="section main d-flex align-items-center">
          <Form className="vw-100" onSubmit={(e) => {
              this.doAction(e)
            }} ref={(form) => {
              this.formElem = form
            }}>
            <Form.Group controlId="formEmail">
              <Form.Label>Email</Form.Label>
              <Form.Control type="email" placeholder="Adresa de email" ref={(input) => {
                  this.emailInput = input
                }}/>
            </Form.Group>
            <Button className="mr-2" variant="black" type="submit">Recupereaza parola</Button>
          </Form>

        </Col>
      </Row>
    </Container>);
  }
}

export default ForgotPassword;
