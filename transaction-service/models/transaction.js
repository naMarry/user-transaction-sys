const mongoose = require('mongoose');

const TransactionSchema = new mongoose.Schema({
  user_id: {
    type: Number,
    required: true
  },
  type: {
    type: String,
    enum: ['deposit', 'withdraw'],
    required: true
  },
  amount: {
    type: mongoose.Schema.Types.Decimal128,
    required: true
  },
  balance: {
    type: mongoose.Schema.Types.Decimal128,
    required: true
  },
  created_at: {
    type: Date,
    default: Date.now
  }
});

module.exports = mongoose.model('TransactionDB', TransactionSchema);
