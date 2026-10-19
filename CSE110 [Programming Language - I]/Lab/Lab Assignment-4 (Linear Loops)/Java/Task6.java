import java.util.Scanner;

public class Task6 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Input: ");
        int num = sc.nextInt();
        // Prime checking
        int divisorCount = 0;
        for (int i = 1; i <= num; i++) 
        {
            if (num % i == 0) 
            {
                divisorCount++;
            }
        }
        if (divisorCount == 2) 
        {
            System.out.println(num + " is a prime number");
        } 
        else {
            System.out.println(num + " is not a prime number");
        }

        // Perfect checking
        int sum = 0;
        for (int i = 1; i < num; i++) 
        {
            if (num % i == 0) 
            {
                sum += i;
            }
        }
        if (sum == num) 
        {
            System.out.println(num + " is a perfect number");
        } 
        else {
            System.out.println(num + " is not a perfect number");
        }
    }
}
