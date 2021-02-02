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

import {app} from '../../base'

class Login extends Component {
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
  doAction(event) {
    event.preventDefault();
    const email = this.emailInput.value
    const password = this.passwordInput.value

    if (!email || !password) {
      this.toaster.show({intent: Intent.DANGER, message: "Adresa de email/parola invalida"});
      return false;
    }

    /* Initializarea metodei de autentificare firebase*/
    app.auth().signInWithEmailAndPassword(email, password).then((user) => {
      if (user && user.email) {
        this.formElem.reset();
        this.props.setLoading(true);
        this.setState({redirect: true && !this.props.loading});
      }
    }).catch((error) => {
      this.toaster.show({intent: Intent.DANGER, message: error.message})
    })
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
            <h2 className="sidebar_title">Bun venit!</h2>
            <p className="sidebar_desc">Te rugam sa te autentifici in formularul alaturat.</p>
          </Container>
        </Col>
        <Col xs={12} md={6} xl={4} className="section main d-flex align-items-center">
          <Form className="vw-100" onSubmit={(event) => {
              this.doAction(event)
            }} ref={(form) => {
              this.formElem = form
            }}>
            <Form.Group controlId="formBasicEmail">
              <Form.Label>Email</Form.Label>
              <Form.Control type="email" placeholder="Email" ref={(input) => {
                  this.emailInput = input
                }}/>
            </Form.Group>
            <Form.Group controlId="formBasicPassword">
              <Form.Label>Parola</Form.Label>
              <Form.Control type="password" placeholder="Parola" ref={(input) => {
                  this.passwordInput = input
                }}/>
            </Form.Group>
            <Button className="mr-2" variant="black" type="submit">Autentifica-te!</Button>
            <Link className="btn btn-outline-dark" to="/register">Inregistrare</Link>
            <Link className="btn-forgot" to="/forgot-password">Ai uitat parola?</Link>
          </Form>
        </Col>
      </Row>
    </Container>);
  }
}

export default Login;
