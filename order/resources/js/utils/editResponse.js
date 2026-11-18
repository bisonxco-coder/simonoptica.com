export default class {
    messages = []
    addMessage(message) { this.messages.push(message) }
    addMessages(messages) { this.messages.push(...messages) }
    hasMessages() { return this.messages.length > 0 }
    getMessage() { return this.messages.join("|") }
}