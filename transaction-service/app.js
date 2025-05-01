const express = require('express')
const cors = require('cors')
const app = express()
require('dotenv').config()

app.use(cors({
    origin: '*', 
    methods: ['GET', 'POST', 'PUT', 'DELETE'],
    allowedHeaders: ['Content-Type', 'Authorization'],
}));
app.use(express.json());

//db connection
const connectDB = require('./config/db');
connectDB();

//routing
const userRoute = require('./routes/transaction')
app.use('/api/transaction', userRoute)

const PORT = process.env.PORT || 8000
app.listen(PORT, () => {
    console.log(`Server is running on PORT ${PORT}`);
})