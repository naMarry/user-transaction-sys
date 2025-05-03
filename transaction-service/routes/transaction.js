const express = require('express')
const router = express.Router()

const { depositBalance, withdrawBalance, getHistory } = require('../controllers/transactionController')

router.get('/history', getHistory)
router.post('/deposit', depositBalance)
router.post('/withdraw', withdrawBalance)

module.exports = router