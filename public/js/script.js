console.log('NIA Asset Monitoring System loaded.');

// this previously called `new bootstrap.Toast(el)` on any element with
// class="toast". No element in the app actually uses that class (main.php's
// toast container renders its own plain divs and dismisses/auto-removes them
// with inline JS), so this was dead code .. i removed it along with the rest of the
// Bootstrap JS dependency.