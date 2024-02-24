We provide convenient financial services through Western Union for money transfers. Our mobile app guides users through two main steps:

1. Initiation (Preview API): Users submit transaction requests, promptly recorded in our database as pending transactions.

2. Confirmation (Confirm API): Users then authenticate and finalize transactions on the confirmation screen. Once confirmed, transactions are marked as approved in our system.

Following transaction approval, a new task emerged: implementing quota limits for recipient countries within specific time frames. For instance, limiting transfers to the UAE to 1 million per week.

Initially, I considered utilizing the confirm API to query transaction amounts approved
for each country within the period. However, a potential issue arose concerning concurrent requests, 
leading to surpassing the set limits.
 
 Example: 
 
 - the remaining quota is 500 and three concurrent requests come with the amount of 500 all of them will pass.

To address this, I devised a priority-based approach:

In the Preview API:

1. Calculate the total approved amount (AA).
2. Calculate the total pending amount in the last 10 minutes (PA).
3. If (AA + new transaction amount > quota), return an error and refrain from inserting new transactions into the database.
4. If (AA + PA + new transaction amount > quota), return an error and avoid inserting new transactions into the database.
5. Otherwise, insert pending transactions into the database.

Now, only pending transactions from the last 10 minutes can proceed to the confirmation screen.

In the Confirm API:

1. Calculate the remaining quota (quota - approved amount). (10000 - 9500 = 500)
2. Select all pending transactions from the last 10 minutes with cumulative amounts.
   
   Example:
   
   - Transaction 4: Amount 500, Cumulative Amount 500
   - Transaction 5: Amount 500, Cumulative Amount 1000
   - Transaction 6: Amount 500, Cumulative Amount 1500

Now, Transaction 4 passes since the cumulative amount is less than the remaining quota, while Transactions 5 and 6 fail due to quota limitations.
