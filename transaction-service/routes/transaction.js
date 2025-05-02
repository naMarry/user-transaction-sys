const express = require('express')
const router = express.Router()
const axios = require('axios');

const TransactionDB = require('../models/transaction');
const BASE_USER_SERVICE_URL = 'http://localhost:8080/php/banking-sys/user-service/api';

router.get('/get', async (req, res) => {
    try {
        const token = req.headers['authorization'];

        const response = await axios.get(`${BASE_USER_SERVICE_URL}/check-login-user`,
            { headers: { 'Content-Type': 'application/json', 'Authorization': token } }
        );

        const data = await response.data;
        const id = await data.id;

        data ? res.status(201).json({ success: true, message: 'get', id: id })
            : res.status(400).json({ success: false, message: 'can not get data ', id: null })
    } catch (error) {
        console.error('Deposit failed:', error.message);
        res.status(500).json({ success: false, message: error.message })
    }
})

const loginUserId = async (req) => {
    const authHeader = req.headers['authorization'];
    if (!authHeader) {
        return false;
    }

    const token = authHeader.split(' ')[1];

    const response = await axios.get(`${BASE_USER_SERVICE_URL}/check-login-user`,
        { headers: { 'Content-Type': 'application/json', 'Authorization': token } }
    );
    const id = await response.data.id;
    return { token: token, id: id }
}

const getBalance = async (token, id) => {
    const balanceRes = await axios.get(`${BASE_USER_SERVICE_URL}/get-balance/${id}`,
        { headers: { 'Content-Type': 'application/json', 'Authorization': token } }
    );
    const balanceData = await balanceRes.data;
    const balance = await balanceData.balance;
    return { balance: balance }
}

router.post('/deposit', async (req, res) => {
    try {
        const { amount } = req.body
        const { token, id } = await loginUserId(req)
        const { balance } = await getBalance(token, id)

        const updateRes = await axios.post(`${BASE_USER_SERVICE_URL}/deposit-balance/${id}`,
            { amount: amount },
            { headers: { 'Content-Type': 'application/json', 'Authorization': token } }
        );

        if (updateRes.data.success !== true) {
            throw new Error('Balance deposit failed');
        }

        const createDeposit = new TransactionDB({
            user_id: id,
            type: "deposit",
            amount: amount,
            balance: balance,
            created_at: new Date()
        });

        const deposit = await createDeposit.save();
        deposit ? res.status(201).json({ result: true, message: "Deposit successfully", balance: balance })
            : res.status(403).json({ result: false, message: "Unable to deposit" })
    } catch (error) {
        console.error('Deposit failed:', error.message);
        res.status(500).json({ success: false, message: error.message })
    }
})

router.post('/withdraw', async (req, res) => {
    try {
        const { amount } = req.body
        const { token, id } = await loginUserId(req)
        const { balance } = await getBalance(token, id)

        const updateRes = await axios.post(`${BASE_USER_SERVICE_URL}/withdraw-balance/${id}`,
            { amount: amount },
            { headers: { 'Content-Type': 'application/json', 'Authorization': token } }
        );

        if (updateRes.data.success !== true) {
            console.log(updateRes);
            
            throw new Error('Balance withdraw failed');
        }

        const createWithdraw = new TransactionDB({
            user_id: id,
            type: "withdraw",
            amount: amount,
            balance: balance,
            created_at: new Date()
        });

        const withdraw = await createWithdraw.save();
        withdraw ? res.status(201).json({ result: true, message: "Withdraw successfully", balance: balance })
            : res.status(403).json({ result: false, message: "Unable to withdraw" })
    } catch (error) {
        console.error('Withdraw failed:', error.message);
        res.status(500).json({ success: false, message: error.message });
    }

})

router.get('/history', async (req, res) => {
    try {
        const history = await TransactionDB.find();
        history ? res.status(201).json({ result: true, message: "Transaction history", history: history })
            : res.status(403).json({ result: false, message: "Unable to get history" })
    } catch (error) {
        console.error('Get history failed:', error.message);
        res.status(500).json({ success: false, message: error.message });
    }
})

module.exports = router