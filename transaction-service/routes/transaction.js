const express = require('express')
const router = express.Router()
const axios = require('axios');

const TransactionDB = require('../models/transaction');
const BASE_USER_SERVICE_URL = 'http://localhost:8080/php/banking-sys/user-service/api';

router.put('/deposit', async (req, res) => {
    try {
        const { user_id, amount } = req.body;        
        // const token = req.headers['authorization'];

        const balanceRes = await axios.get(`${BASE_USER_SERVICE_URL}/get-balance/${user_id}`);
        const balanceData = await balanceRes.data;
        const balance = balanceData.balance;

        const newBalance = Number(balance + amount);
        console.log(newBalance);

        // update balance in PHP
        const updateRes = await axios.put(`${BASE_USER_SERVICE_URL}/update-balance/${user_id}`,
            // { balance: newBalance },
            { balance: newBalance },
            { headers: { 'Content-Type': 'application/json' } }
        );

        if (updateRes.data.success !== true) {
            console.log(updateRes);
            throw new Error('Balance update failed');
        }

        // const createDeposit = new TransactionDB({
        //     user_id: user_id,
        //     type: "deposit",
        //     amount: amount,
        //     balance: newBalance,
        //     created_at: new Date()
        // });

        // const deposit = await createDeposit.save();
        // deposit ? res.status(201).json({ result: true, message: "Deposit successfully", balance: newBalance })
        //     : res.status(403).json({ result: false, message: "Unable to deposit" })
    } catch (error) {
        console.error('Deposit failed:', error.message);
        return { success: false, message: error.message };
    }

})

module.exports = router