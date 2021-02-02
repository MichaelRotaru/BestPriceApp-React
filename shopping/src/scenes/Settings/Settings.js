import React, {Component} from 'react'
import {Redirect} from 'react-router-dom'
import {Toaster, Intent} from '@blueprintjs/core'
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
import {Link} from 'react-router-dom';
import Navigator from '../../components/Navigator';

import {app, base} from '../../base';

class Settings extends Component {
  constructor(props) {
    super(props);
    this.state = {
      currentUser: this.props.currentUser,
      currentUserProfile: this.props.currentUserProfile || {}
    };
  }

  /**
   * Metoda asociata evenimentului de demontare a unei componente
   *
   * @return void
   */
  componentWillUnmount() {
    base.removeBinding(this.state.currentUserProfile);
  }

  /**
   * Proceseaza evenimentul onChange la unui camp de tipul input
   *
   * @param event e:Eveniment
   */
  onChange(e) {
    let {currentUserProfile} = this.state;
    currentUserProfile[e.target.name] = e.target.value;
    this.setState({currentUserProfile})
  }

  /**
   * Proceseaza trimiterea formularului
   *
   * @param event e:Eveniment
   */
  onClick(e) {
    let that = this;
    e.preventDefault();
    let {currentUser, currentUserProfile} = this.state;

    /* Verifica validitatea parolei */
    if(!currentUserProfile.oldpass || !currentUserProfile.newpass){
      if(currentUserProfile.oldpass || currentUserProfile.newpass){
        that.toaster.show({intent: Intent.DANGER, message: "Parola invalida"});
        return;
      }
    }

    /* Incearca actualizarea datelor in baza de date */
    base.update(`users/${this.state.currentUser.uid}`, {
      data: {
        name: currentUserProfile && currentUserProfile.name
          ? currentUserProfile.name
          : ''
      }
    }).then(() => {
      if(currentUserProfile.oldpass && currentUserProfile.newpass){
        /* Verifica validitatea parolei vechi */
        app.auth().signInWithEmailAndPassword(currentUser.email, currentUserProfile.oldpass)
            .then(function(user) {
                /* Modificarea parolei + validarea datelor */
                app.auth().currentUser.updatePassword(currentUserProfile.newpass).then(function(){
                    that.toaster.show({intent: Intent.SUCCESS, message: "Salvare cu success a datelor si a parolei"})
                }).catch(function(err){
                    that.toaster.show({intent: Intent.DANGER, message: err.message})
                });
            }).catch(function(err){
                that.toaster.show({intent: Intent.DANGER, message: err.message})
            });
      }else{
        that.toaster.show({intent: Intent.SUCCESS, message: "Salvare cu succes"})
      }
    }).catch(err => {
      that.toaster.show({intent: Intent.DANGER, message: err.message})
    });
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    let {currentUser, currentUserProfile} = this.state;
    return (<Container fluid="fluid" className="p-0">
      <Navigator isSettingsPage="true"/>
      <Toaster ref={(element) => {
          this.toaster = element
        }}/>
      <Row>
        <Col xs={12} md={6} xl={4} className="section has-menu yellow d-flex align-items-center">
          <Container fluid className="sidebar_wp">
            <h2 className="sidebar_title">Setari utilizator</h2>
            <p className="sidebar_desc">Setarile contului tau.</p>
            <Link className="btn btn-outline-dark" to="/logout">Deconecteaza-te</Link>
          </Container>
        </Col>
        <Col xs={12} md={6} className="section main d-flex align-items-center">
          <Form className="vw-100">
            <Form.Group controlId="formBasicEmail">
              <Form.Label>Email</Form.Label>
              <Form.Control plaintext="plaintext" readOnly="readOnly" defaultValue={currentUser.email}/>
            </Form.Group>
            <Form.Group controlId="formBasicEmail">
              <Form.Label>Nume</Form.Label>
              <Form.Control type="text" placeholder="Introdu numele" name="name" onChange={this.onChange.bind(this)} value={currentUserProfile.name || ''}/>
            </Form.Group>
            <Form.Group controlId="formBasicOldPassword">
              <Form.Label>Vechea Parola</Form.Label>
              <Form.Control type="password" placeholder="Introdu vechea parola" name="oldpass" onChange={this.onChange.bind(this)}/>
            </Form.Group>
            <Form.Group controlId="formBasicNewPassword">
              <Form.Label>Noua Parola</Form.Label>
              <Form.Control type="password" placeholder="Introdu noua parola" name="newpass" onChange={this.onChange.bind(this)} />
            </Form.Group>
            <Button variant="black" type="submit" onClick={this.onClick.bind(this)}>Salveaza</Button>
          </Form>
        </Col>
      </Row>
    </Container>);
  }
}

export default Settings;
