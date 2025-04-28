import "./Footer.css"

function Footer() {

    let yearDate = new Date()
    return ( 
        <footer>
            &copy; All Rights Reserved. {yearDate.getFullYear()}
        </footer>
     );
}

export default Footer;