import React, {Component} from 'react';
import Login from './scenes/Sign/Login';
import Register from './scenes/Sign/Register';
import Logout from './scenes/Sign/Logout';
import ForgotPassword from './scenes/Sign/ForgotPassword';
import Dashboard from './scenes/Dashboard/Dashboard';
import Settings from './scenes/Settings/Settings';
import Navigator from './components/Navigator';
import {Container, Row, Col} from 'react-bootstrap';
import * as routes from './constants/routes';
import {Redirect, BrowserRouter, Route} from 'react-router-dom';
import {Spinner} from '@blueprintjs/core';
import {app, base} from './base';

import "./sass/_theme.scss"

function AuthenticatedRoute({
  component: Component,
  authenticated,
  ...rest
}) {
  return (<Route {...rest} render={(
      props) => authenticated === true
      ? <Component {...props} {...rest}/>
      : <Redirect to={{
          pathname: '/login',
          state: {
            from: props.location
          }
        }}/>}/>)
}

class App extends Component {
  constructor() {
    super();
    this.state = {
      authenticated: false,
      currentUser: null,
      currentUserProfile: null,
      loading: true
    };
    this.setLoading = this.setLoading.bind(this);
    this.setUserState = this.setUserState.bind(this);
  }

  componentWillMount() {
    document.title = 'Shopping Lists'
    this.removeAuthListener = app.auth().onAuthStateChanged((user) => {
      this.setUserState(user);
    })
  }

  componentWillUnmount() {
    this.removeAuthListener();
  }

  setUserState(user) {
    if (user) {
      base.fetch(`users/${user.uid}`, {
        context: this,
        then(data) {
          this.setLoading(false);
          this.setState({authenticated: true, currentUser: user, currentUserProfile: data});
        }
      });
    } else {
      this.setLoading(false);
      this.setState({authenticated: false, currentUser: null})
    }
  }

  setLoading(value) {
    this.setState({loading: value})
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    let {currentUser, currentUserProfile, authenticated, loading} = this.state;

    if (loading === true) {
      return (<Container fluid="fluid" className="loader yellow d-flex justify-content-center align-items-center">
        <Spinner/>
      </Container>)
    }

    var that = this;
    return (
      <Container fluid className="page p-0">
        <BrowserRouter>
            <Container fluid className="main">
                <Route exact path={routes.SIGN_IN} render={(props) => {
                  return <Login setLoading={that.setLoading} authenticated={authenticated&&!loading}{...props}/>
                }} />
                <Route exact  path={routes.REGISTER} render={(props) => {
                  return <Register setLoading={that.setLoading} authenticated={authenticated&&!loading}{...props}/>
                }} />
                <Route exact  path={routes.FORGOT} render={(props) => {
                  return <ForgotPassword authenticated={authenticated&&!loading}{...props}/>
                }} />
                <Route exact path={routes.SIGN_OUT} component={Logout} />
                <AuthenticatedRoute exact path={routes.LANDING} component={Dashboard} currentUserProfile={currentUserProfile} currentUser={currentUser} authenticated={authenticated}/>
                <AuthenticatedRoute exact path={routes.SETTINGS} component={Settings} currentUserProfile={currentUserProfile} currentUser={currentUser} authenticated={authenticated}/>
            </Container>
        </BrowserRouter>
      </Container>
    );
  }
}

export default App;
