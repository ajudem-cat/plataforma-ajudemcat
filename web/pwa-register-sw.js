var swsource="https://ajudem.cat/pwa-sw.js";
function PWAforwpreadCookie(name){var nameEQ=name+"=";var ca=document.cookie.split(";");for(var i=0;i<ca.length;i++){var c=ca[i];while(c.charAt(0)==" ")c=c.substring(1,c.length);if(c.indexOf(nameEQ)==0)return c.substring(nameEQ.length,c.length);}
return null;}
if("serviceWorker"in navigator){window.addEventListener('load',function(){navigator.serviceWorker.register(swsource,{scope:'https://ajudem.cat/'}).then(function(reg){console.log('Congratulations!!Service Worker Registered ServiceWorker scope: ',reg.scope);}).catch(function(err){console.log('ServiceWorker registration failed: ',err);});var deferredPrompt;window.addEventListener('beforeinstallprompt',(e)=>{e.preventDefault();
deferredPrompt=e;if(deferredPrompt!=null||deferredPrompt!=undefined){var a2hsviashortcode=document.getElementsByClassName("pwaforwp-add-via-class");if(a2hsviashortcode!==null){for(var i=0;i<a2hsviashortcode.length;i++){a2hsviashortcode[i].style.display="block";}}
var a2hsviashortcode=document.getElementsByClassName("pwaforwp-sticky-banner");var isMobile=/iPhone|iPad|iPod|Android/i.test(navigator.userAgent);if(a2hsviashortcode!==null&&checkbarClosedOrNot()&&(typeof pwa_cta_assets!=='undefined')&&(pwa_cta_assets.a2h_sticky_on_desktop_cta==1||isMobile)){for(var i=0;i<a2hsviashortcode.length;i++){a2hsviashortcode[i].style.display="flex";}}}});function checkbarClosedOrNot(){var closedTime=PWAforwpreadCookie("pwaforwp_prompt_close")
if(closedTime){var today=new Date();var closedTime=new Date(closedTime);var diffMs=(today-closedTime);var diffMins=Math.round(((diffMs%86400000)%3600000)/60000);if(diffMs){return false;}}
return true;}
var isSafari=/constructor/i.test(window.HTMLElement)||(function(p){return p.toString()==="[object SafariRemoteNotification]";})(!window['safari']||(typeof safari!=='undefined'&&safari.pushNotification));if(isSafari){var a2hsviashortcode=document.getElementsByClassName("pwaforwp-add-via-class");if(a2hsviashortcode!==null){for(var i=0;i<a2hsviashortcode.length;i++){a2hsviashortcode[i].style.display="block";}}
var a2hsviashortcode=document.getElementsByClassName("pwaforwp-sticky-banner");var isMobile=/iPhone|iPad|iPod|Android/i.test(navigator.userAgent);if(a2hsviashortcode!==null&&checkbarClosedOrNot()&&(typeof pwa_cta_assets!=='undefined')&&(pwa_cta_assets.a2h_sticky_on_desktop_cta==1||isMobile)){for(var i=0;i<a2hsviashortcode.length;i++){a2hsviashortcode[i].style.display="flex";}}}
var lastScrollTop = 0;                                        
                              window.addEventListener("scroll", (evt) => {
                                var st = document.documentElement.scrollTop;
                                var closedTime = PWAforwpreadCookie("pwaforwp_prompt_close")
                                    if(closedTime){
                                      var today = new Date();
                                      var closedTime = new Date(closedTime);
                                      var diffMs = (today-closedTime);
                                      var diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes
                                      if(diffMins<4){
                                        return false;
                                      }
                                    }
                                    if (st > lastScrollTop){
                                       if(deferredPrompt !=null){
                                       var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);   if(isMobile){                                                    
                                            var a2hsdesk = document.getElementById("pwaforwp-add-to-home-click");
                                                    if(a2hsdesk !== null  && checkbarClosedOrNot()){
                                                        a2hsdesk.style.display = "block";
                                                    }   
                                        }                                                                 
                                       }                                              
                                    } else {
                                    var bhidescroll = document.getElementById("pwaforwp-add-to-home-click");
                                    if(bhidescroll !== null){
                                    bhidescroll.style.display = "none";
                                    }                                              
                                    }
                                 lastScrollTop = st;  
                                });

                              var addtohomeCloseBtn = document.getElementById("pwaforwp-prompt-close");
                                if(addtohomeCloseBtn !==null){
                                  addtohomeCloseBtn.addEventListener("click", (e) => {
                                      var bhidescroll = document.getElementById("pwaforwp-add-to-home-click");
                                      if(bhidescroll !== null){
                                        bhidescroll.style.display = "none";
                                        document.cookie = "pwaforwp_prompt_close="+new Date();
                                      }                                         
                                  });
                                }
                              var addtohomeBtn = document.getElementById("pwaforwp-add-to-home-click");	
                                if(addtohomeBtn !==null){
                                    addtohomeBtn.addEventListener("click", (e) => {
                                    addToHome();	
                                });
                                }

var a2hsviashortcode=document.getElementsByClassName("pwaforwp-add-via-class");if(a2hsviashortcode!==null){for(var i=0;i<a2hsviashortcode.length;i++){a2hsviashortcode[i].addEventListener("click",addToHome);}}
window.addEventListener('appinstalled',(evt)=>{var a2hsviashortcode=document.getElementsByClassName("pwaforwp-add-via-class");if(a2hsviashortcode!==null){for(var i=0;i<a2hsviashortcode.length;i++){a2hsviashortcode[i].style.display="none";}}
var a2hsviashortcode=document.getElementsByClassName("pwaforwp-sticky-banner");if(a2hsviashortcode!==null){for(var i=0;i<a2hsviashortcode.length;i++){a2hsviashortcode[i].style.display="none";}}});function addToHome(){if(!deferredPrompt){return;}
deferredPrompt.prompt();deferredPrompt.userChoice.then((choiceResult)=>{if(choiceResult.outcome==="accepted"){document.getElementById("pwaforwp-add-to-home-click").style.display = "none";
var a2hsviashortcode=document.getElementsByClassName("pwaforwp-add-via-class");if(a2hsviashortcode!==null){for(var i=0;i<a2hsviashortcode.length;i++){a2hsviashortcode[i].style.display="none";}}
var a2hsviashortcode=document.getElementsByClassName("pwaforwp-sticky-banner");if(a2hsviashortcode!==null){for(var i=0;i<a2hsviashortcode.length;i++){a2hsviashortcode[i].style.display="none";}}
console.log("User accepted the prompt");}else{console.log("User dismissed the prompt");}
deferredPrompt=null;});}});}