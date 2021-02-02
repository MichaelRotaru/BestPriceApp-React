import React, {Component} from 'react';
import Navbar from 'react-bootstrap/Navbar';
import Nav from 'react-bootstrap/Nav';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome'
import {faCog, faArrowLeft, faSignOutAlt, faBars, faTimes} from '@fortawesome/free-solid-svg-icons'
import {Link} from 'react-router-dom';
import {Container, Row, Col} from 'react-bootstrap';

class Navigator extends Component {
  constructor(props) {
    super(props);
    this.state = {
      mobileMenuOpened:false
    }
    this.toggleMobileMenu = this.toggleMobileMenu.bind(this);
  }

  /**
   * Ascunderea/Afisarea meniului in versiunea mobile
   *
   * @param object e: eveniment
   * @return void
   */
  toggleMobileMenu(e){
    e.preventDefault();
    let {mobileMenuOpened} = this.state;
    mobileMenuOpened = !mobileMenuOpened;
    this.setState({mobileMenuOpened});
    this.props.toggleMobileMenu(mobileMenuOpened);
  }

  /**
   * Randarea componentei
   *
   * @return void
   */
  render() {
    let menu;
    if (this.props.isSettingsPage) {
      menu = <Nav>
        <Link to="/" className="nav-link nav-main-btn"><FontAwesomeIcon size="lg" icon={faArrowLeft}/></Link>
      </Nav>;
    } else {
      menu = <Nav>
        <Link to="/" className="nav-link nav-main-btn lists-menu-btn" onClick={(e) => {this.toggleMobileMenu(e)}}><FontAwesomeIcon size="lg" icon={this.state.mobileMenuOpened?faTimes:faBars}/></Link>
        <Link to="/settings" className="nav-link nav-main-btn"><FontAwesomeIcon className="rotate" size="lg" icon={faCog}/></Link>
        <Navbar.Text>{this.props.userName}</Navbar.Text>
        <Link className="nav-link nav-secondary-btn" to="/logout"><FontAwesomeIcon icon={faSignOutAlt}/></Link>
      </Nav>;
    }
    return (<Container fluid="fluid" className="p-0">
      <Row>
        <Col xs={12} md={6} xl={4} className="navigator yellow">
          {menu}
        </Col>
      </Row>
    </Container>)
  }
}

export default Navigator;
