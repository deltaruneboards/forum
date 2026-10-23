// Modified From: https://bechnokid.com/tutorials/freezeframe/

function isGif(src) {
  return src && src.split('?')[0].toLowerCase().endsWith('.gif');
}

class FreezeImages {
    constructor(options = {}) {
      // Set default params
      this.selector = options.selector
      this.imgCls = "ff-img";
      this.canvasCls = "ff-canvas";
      this.hover = (options.hover === true || options.hover === "true") ? true : false;
  
      // Finds all images with selector class and within elements with the selected class
      //  and creates list
      const imgList = Array.from(document.querySelectorAll(`${this.selector}`)).filter(img => isGif(img.src));
      this.imgList = imgList;
      if (!this.imgList.length) return;
  
      // Loops through all images
      for (const img of this.imgList) {
          if(!img.parentElement.className.includes("freeze")) {
            img.parentElement.className += " freeze";
          }

          // Gives <img> the inactive class, which hides GIF by default
          img.className = `${this.imgCls} ff-inactive`;

          // Creates <canvas> of GIF and copies data of first frame of animation
          let canvas = document.createElement("canvas");
          let ctx = canvas.getContext('2d');

          canvas.className = `${this.canvasCls} ff-active`;
          canvas.width = img.naturalWidth;
          canvas.height = img.naturalHeight;

          ctx.imageSmoothingEnabled = false;
          ctx.webkitImageSmoothingEnabled = false;
          ctx.mozImageSmoothingEnabled = false;
          ctx.oImageSmoothingEnabled = false;
          ctx.msImageSmoothingEnabled = false;
          ctx.drawImage(img, 0, 0, img.naturalWidth, img.naturalHeight);
    
          // Creates container that will hold both <img> and <canvas>
          let wrapper = document.createElement("div");
          wrapper.className = "ff-container";
          if (this.hover) wrapper.classList.add("ff-hover");
    
          // Inserts container with <img> and <canvas> where <img> originally was
          img.parentNode.insertBefore(wrapper, img);
          wrapper.appendChild(img);
          wrapper.appendChild(canvas);
        
      }
    }
  
    start() { // Starts animation
      for (const img of this.imgList) {
        img.className = `${this.imgCls} ff-active`;
        img.nextSibling.className = `${this.canvasCls} ff-inactive`;
      }
    }
  
    stop() { // Stops animation
      for (const img of this.imgList) {
        img.className = `${this.imgCls} ff-inactive`;
        img.nextSibling.className = `${this.canvasCls} ff-active`;
      }
    }
  
    toggle() { // Toggles animation based on current state
      for (const img of this.imgList) {
        let imgNewCls = (img.className.includes('ff-inactive')) ? "ff-active": "ff-inactive";
        let canvasNewCls = (img.className.includes('ff-inactive')) ? "ff-inactive": "ff-active";
  
        img.className = `${this.imgCls} ${imgNewCls}`;
        img.nextSibling.className = `${this.canvasCls} ${canvasNewCls}`;
      }
    }
  }
  

function freezeFrame() {
  window.addEventListener("load", function () {
    const style = document.createElement('style');
    style.textContent = `
      .ff-container {
        display: inline-flex;
        position: relative;
      }

      .ff-container img,
      .ff-container canvas {
        align-self: start;
      }

        .ff-container.ff-hover:hover .ff-active,
        .freeze:hover .ff-active,
        .signature:hover .ff-active {
          position: absolute;
          opacity: 0;
        }

        .ff-container.ff-hover:hover .ff-inactive,
        .freeze:hover .ff-inactive,
        .signature:hover .ff-inactive {
          position: static;
          opacity: 1;
          z-index: 1;
        }

        .ff-inactive {
          position: absolute;
          opacity: 0;
          z-index: -99;
        }
        .sceditor-insertemoticon .ff-container {
          margin: 6px 7px 0 0;
          vertical-align: middle;
        }
          
        .sceditor-insertemoticon .ff-container img {
          margin:0;
        }
        span.group-50, a.group-50, a.group-50:visited {
          animation: none;
        }
        .avatar canvas {
          max-width: 160px;
          max-height: 160px;
        }
        `;
      document.head.appendChild(style);
      
      const f = new FreezeImages (
        {
          selector: ".avatar img, .signature img, .dumb_badge img, .sceditor-insertemoticon img, img.freeze, .freeze img",
          hover: true,
        }
      )
  
      // Set event listeners for all buttons
      for(const el of document.getElementsByClassName('start-img-btn')) {
        el.addEventListener('click', () => f.start());
      }
  
      for(const el of document.getElementsByClassName('stop-img-btn')) {
        el.addEventListener('click', () => f.stop());
      }
  
      for(const el of document.getElementsByClassName('toggle-img-btn')) {
        el.addEventListener('click', () => f.toggle());
      }
    
  });   
}