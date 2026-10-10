            window.onscroll = function(){

                const header = document.querySelector(".site-header");

                if(window.scrollY > 50){

                    header.style.background = "rgba(15,23,42,0.5)";

                }else{

                    header.style.background = "rgba(15,23,42,0.95)";

                }

            }
