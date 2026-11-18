import helpers from "./helpers";
import * as PIXI from 'pixi.js'

/**
 * Create a new PIXI js app canvas object
 * @param {string} drawingContainerID id of the html element, to hold the canvas
 * @returns 
 */
export function createNewPixiApp (drawingContainerID) {
  if (!drawingContainerID) return

  try {
    const node = document.getElementById(drawingContainerID)
    if (node === undefined || node === null) return null

    const app = new PIXI.Application({
      backgroundAlpha: 0,
      antialias: true,
    });
    node.appendChild(app.view);
    app.view.style.touchAction = 'auto';

    // Circle resolution. Smaller values increase resolution
    PIXI.GRAPHICS_CURVES.maxLength = 5

    // Create container to hold all graphics and text
    const container = new Container()

    // Create graphics and text objects
    const graphics = new Graphics()
    const text = new TextManager()

    // Add both to container
    container.addChild(graphics)
    container.addChild(text.container)

    // Add container to app stage
    app.stage.addChild(container)

    // Scale canvas to parent
    app.renderer.resize(app.view.parentNode.clientWidth, app.view.parentNode.clientHeight)

    // Move container to center of window
    container.transform.position.x = app.screen.width/2
    container.transform.position.y = app.screen.height/2

    // Pass function to reset app
    const reset = () => {
      const node = document.getElementById(drawingContainerID)
      if (node === undefined || node === null) return null
      node.innerHTML = ''
      node.appendChild(app.view)

      graphics.clear()
      while(text.container.children[0]) {
        text.container.removeChild(text.container.children[0]);
      }
    }
    return {app, container, graphics, text, reset}
  } catch (error) {
    console.error(error)
    return null
  }
}

/**
 * List of common colors
 */
export const colors = {
  'white': helpers.RGBToHex([255,255,255]),
  'red': helpers.RGBToHex([255,0,0]),
  'darkgreen': helpers.RGBToHex([0,100,0]),
  'blue': helpers.RGBToHex([0,0,255]),
  'yellow': helpers.RGBToHex([255,255,0]),
}

export class Container extends PIXI.Container {}

export const roundCap = PIXI.LINE_CAP.ROUND
export const buttCap = PIXI.LINE_CAP.BUTT

export class Graphics extends PIXI.Graphics {
  /**
   * Helper to draw a line from-to
   * @param {number} x0
   * @param {number} y0
   * @param {number} x1
   * @param {number} y1
   */
  drawLine(x0, y0, x1, y1) {
    this.moveTo(x0, y0);
    this.lineTo(x1, y1);
  }

  drawCrispLine(x0, y0, x1, y1) {
    this.line.alignment = 0
    this.drawLine(x0, y0, x1, y1)
  }

  arc(...args) {
    super.arc(...args)
    this.endFill()
  }

  drawEllipse(...args) {
    super.drawEllipse(...args)
    this.endFill()
  }
}

/**
 * Helper class for adding text to the canvas
 */
export class TextManager {
    constructor() {
        this.container = new Container()
        return this
    }

    /**
     * Add text to x,y position
     * @param {string} text 
     * @param {number} x 
     * @param {number} y 
     */
    drawString(text, x, y, color = colors.darkgreen) {
        const resolution = 3 // Higher values = more resolution
        const style = new PIXI.TextStyle({
            fontFamily: 'verdana,geneva,helvetica,sans-serif',
            fontSize: 14 * resolution,
            fill: color,
        });
        const basicText = new PIXI.Text(text, style);
        basicText.anchor.set(0.5, 0.5);

        basicText.x = x * 1.03;
        basicText.y = y * 1.04;

        basicText.transform.scale.set(1 / resolution)
        this.container.addChild(basicText);
    }
}


