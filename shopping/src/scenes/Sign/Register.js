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

class Register extends Component {
  constructor(props) {
    super(props);
    this.state = {
      profile: [],
      redirect: this.props.authenticated
    }
    /* Initializarea metodelor publice ale componentei */
    this.doAction = this.doAction.bind(this);
    this.addUserProfile = this.addUserProfile.bind(this);
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
    let name = this.nameInput.value
    let email = this.emailInput.value
    let emailConfirmation = this.emailConfirmationInput.value
    let password = this.passwordInput.value
    let profile = {
      name: name,
      email: email,
      name: name
    };
    if (!email || !password) {
      this.toaster.show({intent: Intent.DANGER, message: "Adresa de email/parola invalida"});
      return false;
    }

    if (email != emailConfirmation) {
      this.toaster.show({intent: Intent.DANGER, message: "Adresele de email nu se potrivesc"});
      return false;
    }

    /* Initializarea metodei de creare cont firebase*/
    app.auth().createUserWithEmailAndPassword(email, password).then((user) => {
      if (user && user.email) {
        this.formElem.reset();
        this.addUserProfile(profile, user.uid);
      }
    }).catch(error => {
      this.toaster.show({intent: Intent.DANGER, message: error.message})
    });
  }

  addUserProfile(profile, uid) {
    base.update(`users/${uid}`, {
      context: this,
      data: profile
    }).catch(error => {
      this.toaster.show({intent: Intent.DANGER, message: error.message})
    });
    this.props.setLoading(true);
    this.setState({profile});
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
            <h2 className="sidebar_title">Inregistrare!</h2>
            <p className="sidebar_desc">Te rugam sa te autentifici in formularul alaturat.</p>
          </Container>
        </Col>
        <Col xs={12} md={6} xl={4} className="section main d-flex align-items-center">
          <Form className="vw-100" onSubmit={(e) => {
              this.doAction(e)
            }} ref={(form) => {
              this.formElem = form
            }}>
            <Form.Group controlId="formName">
              <Form.Label>Nume</Form.Label>
              <Form.Control type="text" placeholder="Nume complet" ref={(input) => {
                  this.nameInput = input
                }}/>
            </Form.Group>
            <Form.Group controlId="formEmail">
              <Form.Label>Email</Form.Label>
              <Form.Control type="email" placeholder="Adresa de email" ref={(input) => {
                  this.emailInput = input
                }}/>
            </Form.Group>
            <Form.Group controlId="formEmailConfirmation">
              <Form.Label>Confirmare email</Form.Label>
              <Form.Control type="email" placeholder="Confirma adresa de email" ref={(input) => {
                  this.emailConfirmationInput = input
                }}/>
            </Form.Group>
            <Form.Group controlId="formPassword">
              <Form.Label>Parola</Form.Label>
              <Form.Control type="password" placeholder="Parola" ref={(input) => {
                  this.passwordInput = input
                }}/>
            </Form.Group>
            <Button variant="black" className="mr-2" type="submit">Inregistreaza-te!</Button>
            <Link className="btn btn-outline-dark" to="/login">Autentificare</Link>
          </Form>

        </Col>
      </Row>
    </Container>);
  }
}

export default Register;
