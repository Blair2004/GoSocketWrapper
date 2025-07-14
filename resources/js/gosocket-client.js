/**
 * GoSocket Client Library
 * Provides WebSocket communication with GoSocket server
 */
class GoSocketClient {
    constructor(options = {}) {
        this.options = {
            url: options.url || 'ws://localhost:8080',
            debug: options.debug || false,
            pingInterval: options.pingInterval || 30000,
            reconnectAttempts: options.reconnectAttempts || 5,
            reconnectDelay: options.reconnectDelay || 1000,
            token: options.token || null,
            ...options
        };

        this.socket = null;
        this.isConnected = false;
        this.reconnectCount = 0;
        this.pingTimer = null;
        this.eventListeners = {};
        this.authenticated = false;
        this.userId = null;
    }

    /**
     * Connect to the socket server
     */
    connect() {
        try {
            this.socket = new WebSocket(this.options.url);
            this.setupEventHandlers();
            
            if (this.options.debug) {
                console.log('[GoSocket] Connecting to:', this.options.url);
            }
        } catch (error) {
            this.log('Connection error:', error);
            this.handleReconnect();
        }
    }

    /**
     * Setup WebSocket event handlers
     */
    setupEventHandlers() {
        this.socket.onopen = (event) => {
            this.isConnected = true;
            this.reconnectCount = 0;
            this.log('Connected to GoSocket server');
            
            this.startPing();
            this.emit('connected', event);
            
            // Auto-authenticate if token is provided
            if (this.options.token) {
                this.authenticate(this.options.token);
            }
        };

        this.socket.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                this.handleMessage(data);
            } catch (error) {
                this.log('Error parsing message:', error);
            }
        };

        this.socket.onclose = (event) => {
            this.isConnected = false;
            this.authenticated = false;
            this.stopPing();
            this.log('Connection closed:', event.code, event.reason);
            
            this.emit('disconnected', event);
            
            if (!event.wasClean && this.reconnectCount < this.options.reconnectAttempts) {
                this.handleReconnect();
            }
        };

        this.socket.onerror = (error) => {
            this.log('Socket error:', error);
            this.emit('error', error);
        };
    }

    /**
     * Handle incoming messages
     */
    handleMessage(data) {
        this.log('Received:', data);

        switch (data.type) {
            case 'pong':
                this.emit('pong', data);
                break;
            
            case 'authenticated':
                this.authenticated = true;
                this.userId = data.user_id;
                this.emit('authenticated', data);
                break;
            
            case 'error':
                this.emit('error', data);
                break;
            
            case 'message':
                this.emit('message', data);
                break;
            
            case 'channel_joined':
                this.emit('channel_joined', data);
                break;
            
            case 'channel_left':
                this.emit('channel_left', data);
                break;
            
            default:
                this.emit('data', data);
                break;
        }
    }

    /**
     * Authenticate with the server
     */
    authenticate(token) {
        this.send({
            action: 'authenticate',
            data: { token: token }
        });
    }

    /**
     * Join a channel
     */
    joinChannel(channel, isPrivate = false) {
        this.send({
            action: 'join_channel',
            data: { 
                channel: channel,
                private: isPrivate
            }
        });
    }

    /**
     * Leave a channel
     */
    leaveChannel(channel) {
        this.send({
            action: 'leave_channel',
            data: { channel: channel }
        });
    }

    /**
     * Send a message to a channel
     */
    sendMessage(channel, message, data = {}) {
        this.send({
            action: 'send_message',
            data: {
                channel: channel,
                message: message,
                ...data
            }
        });
    }

    /**
     * Send data to the server
     */
    send(data) {
        if (!this.isConnected) {
            this.log('Cannot send data: not connected');
            return false;
        }

        try {
            const payload = JSON.stringify(data);
            this.socket.send(payload);
            this.log('Sent:', data);
            return true;
        } catch (error) {
            this.log('Error sending data:', error);
            return false;
        }
    }

    /**
     * Start ping mechanism
     */
    startPing() {
        this.stopPing();
        this.pingTimer = setInterval(() => {
            if (this.isConnected) {
                this.send({ action: 'ping' });
            }
        }, this.options.pingInterval);
    }

    /**
     * Stop ping mechanism
     */
    stopPing() {
        if (this.pingTimer) {
            clearInterval(this.pingTimer);
            this.pingTimer = null;
        }
    }

    /**
     * Handle reconnection
     */
    handleReconnect() {
        if (this.reconnectCount >= this.options.reconnectAttempts) {
            this.log('Max reconnection attempts reached');
            this.emit('max_reconnect_attempts');
            return;
        }

        this.reconnectCount++;
        this.log(`Reconnecting... (${this.reconnectCount}/${this.options.reconnectAttempts})`);
        
        setTimeout(() => {
            this.connect();
        }, this.options.reconnectDelay * this.reconnectCount);
    }

    /**
     * Add event listener
     */
    on(event, callback) {
        if (!this.eventListeners[event]) {
            this.eventListeners[event] = [];
        }
        this.eventListeners[event].push(callback);
    }

    /**
     * Remove event listener
     */
    off(event, callback) {
        if (!this.eventListeners[event]) return;
        
        const index = this.eventListeners[event].indexOf(callback);
        if (index > -1) {
            this.eventListeners[event].splice(index, 1);
        }
    }

    /**
     * Emit event
     */
    emit(event, data) {
        if (!this.eventListeners[event]) return;
        
        this.eventListeners[event].forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                this.log('Error in event callback:', error);
            }
        });
    }

    /**
     * Disconnect from the server
     */
    disconnect() {
        this.stopPing();
        
        if (this.socket) {
            this.socket.close();
        }
    }

    /**
     * Log messages (if debug is enabled)
     */
    log(...args) {
        if (this.options.debug) {
            console.log('[GoSocket]', ...args);
        }
    }

    /**
     * Get connection status
     */
    getStatus() {
        return {
            connected: this.isConnected,
            authenticated: this.authenticated,
            userId: this.userId,
            reconnectCount: this.reconnectCount
        };
    }
}

// Auto-initialize if window.goSocketConfig is present
if (typeof window !== 'undefined' && window.goSocketConfig) {
    window.goSocket = new GoSocketClient(window.goSocketConfig);
    
    // Auto-connect if specified
    if (window.goSocketConfig.autoConnect !== false) {
        window.goSocket.connect();
    }
}
