const axios = require('axios');

const TransactionDB = require('../models/transaction');
const BASE_USER_SERVICE_URL = 'http://localhost:8080/php/banking-sys/user-service/api';

const loginUserId = async (req) => {
    try {
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
    catch (error) {
        // console.error('Token retrieve failed:', error);
        const message = error?.response?.data?.message || 'Something went wrong';
        res.status(500).json({ success: false, message: message })
    }
}

const getBalance = async (token, id) => {
    try {
        const balanceRes = await axios.get(`${BASE_USER_SERVICE_URL}/get-balance/${id}`,
            { headers: { 'Content-Type': 'application/json', 'Authorization': token } }
        );
        const balanceData = await balanceRes.data;
        const balance = await balanceData.balance;
        return { balance: balance }
    } catch (error) {
        // console.error('Balance retrieve failed:', error);
        const message = error?.response?.data?.message || 'Something went wrong';
        res.status(500).json({ success: false, message: message })
    }
}

const depositBalance = async (req, res) => {
    try {
        const { amount } = req.body
        const { token, id } = await loginUserId(req)

        if(!token || !id) {
            res.status(403).json({ result: false, message: "Unauthorize user! please login first" })
        }

        const updateRes = await axios.post(`${BASE_USER_SERVICE_URL}/deposit-balance/${id}`,
            { amount: amount },
            { headers: { 'Content-Type': 'application/json', 'Authorization': token } }
        );

        if (updateRes.data.success !== true) {
            throw new Error('Balance deposit failed');
        }

        const { balance } = await getBalance(token, id)

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
        // console.error('Deposit failed:', error);
        const message = error?.response?.data?.message || 'Something went wrong';
        res.status(500).json({ success: false, message: message })
    }
}

const withdrawBalance = async (req, res) => {
    try {
        const { amount } = req.body
        const { token, id } = await loginUserId(req)

        if(!token || !id) {
            res.status(403).json({ result: false, message: "Unauthorize user! please login first" })
        }

        const updateRes = await axios.post(`${BASE_USER_SERVICE_URL}/withdraw-balance/${id}`,
            { amount: amount },
            { headers: { 'Content-Type': 'application/json', 'Authorization': token } }
        );

        if (updateRes.data.success !== true) {
            throw new Error('Balance withdraw failed');
        }

        const { balance } = await getBalance(token, id)

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
        // console.error('Withdraw failed:', error.message, error);
        const message = error?.response?.data?.message || 'Something went wrong';
        const balance = error?.response?.data?.balance;
        res.status(500).json({ success: false, message: message, balance: balance });
    }

}

const getHistory = async (req, res) => {
    try {
        const { id } = await loginUserId(req)

        const history = await TransactionDB.find({ user_id: id });
        history.length>0 ? res.status(201).json({ result: true, message: "Transaction history", history: history })
            : res.status(400).json({ result: false, message: "No transaction history", history: [] })
    } catch (error) {
        console.error('Get history failed:', error.message);
        res.status(500).json({ success: false, message: error.message });
    }
}

module.exports = {
    depositBalance,
    withdrawBalance,
    getHistory
}